<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Purchase;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class DepositManagementController extends Controller
{
    public function index(Request $request)
    {
        $deposits = Purchase::query()
            ->with('user')
            ->where('payment_method', 'bank_transfer')
            ->orderByDesc('created_at')
            ->get()
            ->map(fn (Purchase $purchase) => [
                'id' => $purchase->id,
                'user' => [
                    'id' => $purchase->user?->id,
                    'name' => $purchase->user?->name,
                    'email' => $purchase->user?->email,
                    'bank_name' => $purchase->user?->bank_name,
                    'bank_account_name' => $purchase->user?->bank_account_name,
                    'bank_account_number' => $purchase->user?->bank_account_number,
                ],
                'plan_name' => $purchase->plan_name,
                'amount_cents' => $purchase->amount_cents,
                'status' => $purchase->status,
                'created_at' => optional($purchase->created_at)->toDateTimeString(),
            ]);

        return Inertia::render('Admin/Deposits', [
            'deposits' => $deposits,
        ]);
    }

    public function approve(Purchase $purchase): RedirectResponse
    {
        if ($purchase->payment_method !== 'bank_transfer') {
            throw ValidationException::withMessages([
                'deposit' => 'Only bank transfer deposits can be approved.',
            ]);
        }

        if ($purchase->status !== 'pending') {
            throw ValidationException::withMessages([
                'deposit' => 'Deposit is already processed.',
            ]);
        }

        DB::transaction(function () use ($purchase): void {
            $purchaseLocked = Purchase::query()->whereKey($purchase->id)->lockForUpdate()->first();
            if (! $purchaseLocked || $purchaseLocked->status !== 'pending') {
                return;
            }

            $userLocked = User::query()->whereKey($purchaseLocked->user_id)->lockForUpdate()->first();
            if (! $userLocked) {
                return;
            }

            $referrerId = $purchaseLocked->referrer_id;
            $commissionCents = $purchaseLocked->referral_commission_cents;

            if (! $referrerId && $commissionCents === 0 && $userLocked->referrer_id && $userLocked->referrer_id !== $userLocked->id) {
                $referrerLocked = User::query()->whereKey($userLocked->referrer_id)->lockForUpdate()->first();
                if ($referrerLocked) {
                    $commissionCents = (int) round($purchaseLocked->amount_cents * 0.05);
                    if ($commissionCents > 0) {
                        $referrerLocked->balance_cents += $commissionCents;
                        $referrerLocked->save();
                        $referrerId = $referrerLocked->id;
                    }
                }
            }

            $purchaseLocked->status = 'completed';
            $purchaseLocked->referrer_id = $referrerId;
            $purchaseLocked->referral_commission_cents = $commissionCents;
            $purchaseLocked->save();
        });

        return back();
    }
}
