<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Purchase;
use App\Models\User;
use App\Models\WithdrawalRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
                'created_at' => optional($user->created_at)->toDateString(),
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
            ]);

        $recentTransactions = $purchases
            ->concat($withdrawals->map(function (array $withdrawal) {
                return array_merge($withdrawal, [
                    'type' => 'withdrawal',
                    'description' => 'Withdrawal request',
                ]);
            }))
            ->sortByDesc('created_at')
            ->values()
            ->take(30);

        return Inertia::render('Admin/Dashboard', [
            'users' => $users,
            'adminBalanceCents' => $admin?->balance_cents ?? 0,
            'withdrawals' => $withdrawals,
            'recentTransactions' => $recentTransactions,
        ]);
    }

    public function transfer(Request $request, User $user)
    {
        $admin = $request->user();

        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01'],
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

        return back();
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

        Purchase::create([
            'user_id' => $user->id,
            'referrer_id' => null,
            'plan_key' => $data['plan_key'],
            'plan_name' => $plan['name'],
            'daily_interest_bps' => $plan['daily_interest_bps'],
            'duration_days' => $plan['duration_days'],
            'min_amount_cents' => $plan['min_amount_cents'],
            'max_amount_cents' => $plan['max_amount_cents'],
            'amount_cents' => $amountCents,
            'referral_commission_cents' => 0,
            'payment_method' => 'admin_grant',
            'bank_name' => null,
            'status' => 'completed',
        ]);

        return back()->with('success', 'Package sent successfully.');
    }
}
