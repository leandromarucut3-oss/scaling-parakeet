<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\WithdrawalRequest;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class WithdrawalManagementController extends Controller
{
    public function index(Request $request)
    {
        $withdrawals = WithdrawalRequest::query()
            ->with('user')
            ->orderByDesc('created_at')
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

        return Inertia::render('Admin/Withdrawals', [
            'withdrawals' => $withdrawals,
        ]);
    }
    public function approve(WithdrawalRequest $withdrawal): RedirectResponse
    {
        if ($withdrawal->status !== 'pending') {
            throw ValidationException::withMessages([
                'withdrawal' => 'Withdrawal is already processed.',
            ]);
        }

        $withdrawal->status = 'approved';
        $withdrawal->save();

        return back();
    }

    public function reject(WithdrawalRequest $withdrawal): RedirectResponse
    {
        if ($withdrawal->status !== 'pending') {
            throw ValidationException::withMessages([
                'withdrawal' => 'Withdrawal is already processed.',
            ]);
        }

        DB::transaction(function () use ($withdrawal): void {
            $withdrawalLocked = WithdrawalRequest::query()->whereKey($withdrawal->id)->lockForUpdate()->first();
            $userLocked = User::query()->whereKey($withdrawal->user_id)->lockForUpdate()->first();

            if (! $withdrawalLocked || ! $userLocked) {
                return;
            }

            if ($withdrawalLocked->status !== 'pending') {
                return;
            }

            $withdrawalLocked->status = 'rejected';
            $withdrawalLocked->save();

            $userLocked->balance_cents += $withdrawalLocked->amount_cents;
            $userLocked->save();
        });

        return back();
    }
}
