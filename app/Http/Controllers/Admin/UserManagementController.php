<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
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
            ->with('roles')
            ->orderByDesc('created_at')
            ->get()
            ->map(fn (User $user) => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'created_at' => optional($user->created_at)->toDateString(),
                'roles' => $user->getRoleNames(),
                'balance_cents' => $user->balance_cents,
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

        return Inertia::render('Admin/Dashboard', [
            'users' => $users,
            'adminBalanceCents' => $admin?->balance_cents ?? 0,
            'withdrawals' => $withdrawals,
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
}
