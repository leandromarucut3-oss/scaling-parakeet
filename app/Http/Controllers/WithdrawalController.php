<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\WithdrawalRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class WithdrawalController extends Controller
{
    private const MIN_WITHDRAWAL_CENTS = 2000;

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:20'],
        ]);

        $amountCents = (int) round($data['amount'] * 100);

        if ($amountCents < self::MIN_WITHDRAWAL_CENTS) {
            throw ValidationException::withMessages([
                'amount' => 'Minimum withdrawal is 20.00 USD.',
            ]);
        }

        $user = $request->user();

        if (! $user->bank_name || ! $user->bank_account_name || ! $user->bank_account_number) {
            throw ValidationException::withMessages([
                'amount' => 'Add your bank details before requesting a withdrawal.',
            ]);
        }

        DB::transaction(function () use ($user, $amountCents): void {
            $userLocked = User::query()->whereKey($user->id)->lockForUpdate()->first();

            if ($userLocked->balance_cents < $amountCents) {
                throw ValidationException::withMessages([
                    'amount' => 'Insufficient balance.',
                ]);
            }

            $userLocked->balance_cents -= $amountCents;
            $userLocked->save();

            WithdrawalRequest::create([
                'user_id' => $userLocked->id,
                'amount_cents' => $amountCents,
                'status' => 'pending',
                'bank_name' => $userLocked->bank_name,
                'bank_account_name' => $userLocked->bank_account_name,
                'bank_account_number' => $userLocked->bank_account_number,
            ]);
        });

        return back()->with([
            'withdrawal_success' => 'Withdrawal request submitted successfully.',
        ]);
    }
}
