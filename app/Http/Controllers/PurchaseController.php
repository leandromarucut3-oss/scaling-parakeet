<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PurchaseController extends Controller
{
    private const PLANS = [
        'premier' => [
            'name' => 'Premier',
            'min_amount_cents' => 15000,
            'max_amount_cents' => 79900,
            'daily_interest_bps' => 50,
            'duration_days' => 150,
        ],
        'deluxe' => [
            'name' => 'Deluxe',
            'min_amount_cents' => 80000,
            'max_amount_cents' => 799900,
            'daily_interest_bps' => 70,
            'duration_days' => 150,
        ],
        'presidential' => [
            'name' => 'Presidential',
            'min_amount_cents' => 800000,
            'max_amount_cents' => 100000000,
            'daily_interest_bps' => 90,
            'duration_days' => 90,
        ],
    ];

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'plan_key' => ['required', 'string', 'in:'.implode(',', array_keys(self::PLANS))],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'payment_method' => ['required', 'string', 'in:account_balance'],
        ]);

        $amountCents = (int) round($data['amount'] * 100);
        $plan = self::PLANS[$data['plan_key']];

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

        $user = $request->user();

        $purchase = null;

        DB::transaction(function () use ($user, $amountCents, $plan, $data, &$purchase): void {
            $userLocked = User::query()->whereKey($user->id)->lockForUpdate()->first();

            if ($userLocked->balance_cents < $amountCents) {
                throw ValidationException::withMessages([
                    'amount' => 'Insufficient balance.',
                ]);
            }

            $userLocked->balance_cents -= $amountCents;
            $userLocked->save();

                $referrerId = null;
                $commissionCents = 0;

                if ($userLocked->referrer_id && $userLocked->referrer_id !== $userLocked->id) {
                    $referrerLocked = User::query()->whereKey($userLocked->referrer_id)->lockForUpdate()->first();
                    if ($referrerLocked) {
                        $commissionCents = (int) round($amountCents * 0.05);
                        if ($commissionCents > 0) {
                            $referrerLocked->balance_cents += $commissionCents;
                            $referrerLocked->save();
                            $referrerId = $referrerLocked->id;
                        }
                    }
                }

            $purchase = Purchase::create([
                'user_id' => $userLocked->id,
                'referrer_id' => $referrerId,
                'plan_key' => $data['plan_key'],
                'plan_name' => $plan['name'],
                'daily_interest_bps' => $plan['daily_interest_bps'],
                'duration_days' => $plan['duration_days'],
                'min_amount_cents' => $plan['min_amount_cents'],
                'max_amount_cents' => $plan['max_amount_cents'],
                'amount_cents' => $amountCents,
                'referral_commission_cents' => $commissionCents,
                'payment_method' => 'account_balance',
                'status' => 'completed',
            ]);
        });

        return back()->with([
            'success' => 'Purchase completed successfully.',
            'purchase_receipt' => $purchase ? [
                'id' => $purchase->id,
                'plan_name' => $purchase->plan_name,
                'amount_cents' => $purchase->amount_cents,
                'daily_interest_bps' => $purchase->daily_interest_bps,
                'duration_days' => $purchase->duration_days,
                'payment_method' => $purchase->payment_method,
                'created_at' => optional($purchase->created_at)->toDateTimeString(),
            ] : null,
        ]);
    }
}
