<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Purchase;
use App\Models\User;
use App\Models\WithdrawalRequest;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class UserManagementController extends Controller
{
    public function index(Request $request)
    {
        $admin = $request->user();

        $users = User::query()
            ->with(['roles', 'referrer'])
            ->orderByDesc('created_at')
            ->get()
            ->map(fn (User $user) => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'referral_code' => $user->referral_code,
                'created_at' => optional($user->created_at)->toDateString(),
                'is_new' => $user->created_at && $user->created_at->greaterThan(now()->subDay()),
                'roles' => $user->getRoleNames(),
                'balance_cents' => $user->balance_cents,
                'referrer' => $user->referrer ? [
                    'id' => $user->referrer->id,
                    'name' => $user->referrer->name,
                    'email' => $user->referrer->email,
                ] : null,
            ]);

        $withdrawals = WithdrawalRequest::query()
            ->with('user')
            ->orderByDesc('created_at')
            ->limit(50)
            ->get()
            ->map(fn (WithdrawalRequest $withdrawal) => [
                'id' => $withdrawal->id,
                'user' => [
                    'id' => $withdrawal->user?->id,
                    'name' => $withdrawal->user?->name,
                    'email' => $withdrawal->user?->email,
                ],
                'amount_cents' => $withdrawal->amount_cents,
                'status' => $withdrawal->status,
                'bank_name' => $withdrawal->bank_name,
                'bank_account_name' => $withdrawal->bank_account_name,
                'bank_account_number' => $withdrawal->bank_account_number,
                'created_at' => optional($withdrawal->created_at)->toDateTimeString(),
            ]);

        $purchases = Purchase::query()
            ->with('user')
            ->orderByDesc('created_at')
            ->limit(50)
            ->get()
            ->map(fn (Purchase $purchase) => [
                'id' => $purchase->id,
                'user' => [
                    'id' => $purchase->user?->id,
                    'name' => $purchase->user?->name,
                    'email' => $purchase->user?->email,
                ],
                'amount_cents' => $purchase->amount_cents,
                'status' => $purchase->status,
                'created_at' => optional($purchase->created_at)->toDateTimeString(),
                'type' => 'purchase',
                'description' => $purchase->plan_name ? "Package: {$purchase->plan_name}" : 'Package purchase',
                'is_new' => $purchase->status === 'pending',
            ]);

        $recentTransactions = $purchases
            ->concat($withdrawals->map(function (array $withdrawal) {
                return array_merge($withdrawal, [
                    'type' => 'withdrawal',
                    'description' => 'Withdrawal request',
                    'is_new' => false,
                ]);
            }))
            ->sortByDesc('created_at')
            ->values()
            ->take(30);

        return Inertia::render('Admin/Dashboard', [
            'users' => $users,
            'appUrl' => config('app.url'),
        ]);
    }

    public function show(User $user): JsonResponse
    {
        $user->loadMissing('referrer');

        $depositHistory = Purchase::query()
            ->where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->limit(20)
            ->get()
            ->map(fn (Purchase $purchase) => [
                'id' => $purchase->id,
                'amount_cents' => $purchase->amount_cents,
                'status' => $purchase->status,
                'created_at' => optional($purchase->created_at)->toDateTimeString(),
                'plan_name' => $purchase->plan_name,
                'bank_name' => $purchase->bank_name,
            ]);

        $withdrawalHistory = WithdrawalRequest::query()
            ->where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->limit(20)
            ->get()
            ->map(fn (WithdrawalRequest $withdrawal) => [
                'id' => $withdrawal->id,
                'amount_cents' => $withdrawal->amount_cents,
                'status' => $withdrawal->status,
                'created_at' => optional($withdrawal->created_at)->toDateTimeString(),
                'bank_name' => $withdrawal->bank_name,
                'bank_account_name' => $withdrawal->bank_account_name,
                'bank_account_number' => $withdrawal->bank_account_number,
            ]);

        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'created_at' => optional($user->created_at)->toDateTimeString(),
            'balance_cents' => $user->balance_cents,
            'bank_name' => $user->bank_name,
            'bank_account_name' => $user->bank_account_name,
            'bank_account_number' => $user->bank_account_number,
            'referrer' => $user->referrer ? [
                'id' => $user->referrer->id,
                'name' => $user->referrer->name,
                'email' => $user->referrer->email,
            ] : null,
            'deposit_history' => $depositHistory,
            'withdrawal_history' => $withdrawalHistory,
        ]);
    }

    public function destroyDeposit(User $user, Purchase $purchase): JsonResponse
    {
        if ($purchase->user_id !== $user->id) {
            abort(403);
        }

        DB::transaction(function () use ($purchase): void {
            $purchaseLocked = Purchase::query()->whereKey($purchase->id)->lockForUpdate()->first();
            if (! $purchaseLocked) {
                return;
            }

            $purchaseLocked->delete();
        });

        return response()->json(['message' => 'Deposit deleted successfully.']);
    }

    public function sendFunds(Request $request)
    {
        return Inertia::render('Admin/SendFunds', [
            'users' => $this->getUsersList(),
        ]);
    }

    public function sendPackage(Request $request)
    {
        return Inertia::render('Admin/SendPackage', [
            'users' => $this->getUsersList(),
        ]);
    }

    public function recentTransactions(Request $request)
    {
        return Inertia::render('Admin/RecentTransactions', [
            'recentTransactions' => $this->getRecentTransactions(),
        ]);
    }

    private function getUsersList()
    {
        return User::query()
            ->with('referrer')
            ->orderByDesc('created_at')
            ->get()
            ->map(fn (User $user) => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'referral_code' => $user->referral_code,
                'created_at' => optional($user->created_at)->toDateString(),
                'is_new' => $user->created_at && $user->created_at->greaterThan(now()->subDay()),
                'roles' => $user->getRoleNames(),
                'balance_cents' => $user->balance_cents,
                'referrer' => $user->referrer ? [
                    'id' => $user->referrer->id,
                    'name' => $user->referrer->name,
                    'email' => $user->referrer->email,
                ] : null,
            ]);
    }

    private function getRecentTransactions()
    {
        $purchases = Purchase::query()
            ->with('user')
            ->orderByDesc('created_at')
            ->limit(50)
            ->get()
            ->map(fn (Purchase $purchase) => [
                'id' => $purchase->id,
                'user' => [
                    'id' => $purchase->user?->id,
                    'name' => $purchase->user?->name,
                    'email' => $purchase->user?->email,
                ],
                'amount_cents' => $purchase->amount_cents,
                'status' => $purchase->status,
                'created_at' => optional($purchase->created_at)->toDateTimeString(),
                'type' => 'purchase',
                'description' => $purchase->plan_name ? "Package: {$purchase->plan_name}" : 'Package purchase',
                'is_new' => $purchase->status === 'pending',
            ]);

        $withdrawals = WithdrawalRequest::query()
            ->with('user')
            ->orderByDesc('created_at')
            ->limit(50)
            ->get()
            ->map(fn (WithdrawalRequest $withdrawal) => [
                'id' => $withdrawal->id,
                'user' => [
                    'id' => $withdrawal->user?->id,
                    'name' => $withdrawal->user?->name,
                    'email' => $withdrawal->user?->email,
                ],
                'amount_cents' => $withdrawal->amount_cents,
                'status' => $withdrawal->status,
                'created_at' => optional($withdrawal->created_at)->toDateTimeString(),
                'type' => 'withdrawal',
                'description' => 'Withdrawal request',
                'is_new' => false,
            ]);

        return $purchases
            ->concat($withdrawals)
            ->sortByDesc('created_at')
            ->values()
            ->take(50);
    }

    public function transfer(Request $request, User $user)
    {
        $admin = $request->user();

        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01'],
        ]);

        Log::info('Admin transfer requested', [
            'admin_id' => $admin->id,
            'recipient_id' => $user->id,
            'amount' => $data['amount'],
        ]);

        $amountCents = (int) round($data['amount'] * 100);

        if ($amountCents <= 0) {
            throw ValidationException::withMessages([
                'amount' => 'Amount must be at least 0.01.',
            ]);
        }

        DB::transaction(function () use ($admin, $user, $amountCents) {
            $adminLocked = User::query()->whereKey($admin->id)->lockForUpdate()->first();
            $recipientLocked = User::query()->whereKey($user->id)->lockForUpdate()->first();

            if ($adminLocked->balance_cents < $amountCents) {
                throw ValidationException::withMessages([
                    'amount' => 'Insufficient admin funds.',
                ]);
            }

            $adminLocked->balance_cents -= $amountCents;
            $recipientLocked->balance_cents += $amountCents;

            $adminLocked->save();
            $recipientLocked->save();
        });

        Log::info('Admin transfer completed', [
            'admin_id' => $admin->id,
            'recipient_id' => $user->id,
            'amount_cents' => $amountCents,
        ]);

        return back()->with('success', 'Funds transferred successfully.');
    }

    public function grantPackage(Request $request, User $user)
    {
        $plans = config('investment_plans', []);

        $data = $request->validate([
            'plan_key' => ['required', 'string', 'in:'.implode(',', array_keys($plans))],
            'amount' => ['required', 'numeric', 'min:0.01'],
        ]);

        $plan = $plans[$data['plan_key']];
        $amountCents = (int) round($data['amount'] * 100);

        if ($amountCents <= 0) {
            throw ValidationException::withMessages([
                'amount' => 'Amount must be at least 0.01.',
            ]);
        }

        if ($amountCents < $plan['min_amount_cents'] || $amountCents > $plan['max_amount_cents']) {
            throw ValidationException::withMessages([
                'amount' => 'Amount must be within the selected plan range.',
            ]);
        }

        DB::transaction(function () use ($user, $plan, $amountCents, $data): void {
            $referrerId = null;
            $commissionCents = 0;

            if ($user->referrer_id && $user->referrer_id !== $user->id) {
                $referrerLocked = User::query()->whereKey($user->referrer_id)->lockForUpdate()->first();
                if ($referrerLocked) {
                    $commissionCents = (int) round($amountCents * 0.05);
                    if ($commissionCents > 0) {
                        $referrerLocked->balance_cents += $commissionCents;
                        $referrerLocked->save();
                        $referrerId = $referrerLocked->id;
                    }
                }
            }

            Purchase::create([
                'user_id' => $user->id,
                'referrer_id' => $referrerId,
                'plan_key' => $data['plan_key'],
                'plan_name' => $plan['name'],
                'daily_interest_bps' => $plan['daily_interest_bps'],
                'duration_days' => $plan['duration_days'],
                'min_amount_cents' => $plan['min_amount_cents'],
                'max_amount_cents' => $plan['max_amount_cents'],
                'amount_cents' => $amountCents,
                'referral_commission_cents' => $commissionCents,
                'payment_method' => 'admin_grant',
                'bank_name' => null,
                'status' => 'completed',
            ]);
        });

        return back()->with('success', 'Package sent successfully.');
    }

    public function searchUsers(Request $request): JsonResponse
    {
        $query = $request->input('q', '');

        if (strlen($query) < 2) {
            return response()->json([]);
        }

        $users = User::query()
            ->where('name', 'like', "%{$query}%")
            ->orWhere('email', 'like', "%{$query}%")
            ->limit(10)
            ->get()
            ->map(fn (User $user) => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'balance_cents' => $user->balance_cents,
            ]);

        return response()->json($users);
    }
}
