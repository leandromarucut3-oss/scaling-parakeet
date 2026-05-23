<?php

namespace App\Http\Controllers;

use App\Models\Contract;
use App\Models\Purchase;
use App\Models\User;
use App\Services\ContractService;
use App\Services\PackageSlotService;
use App\Jobs\SendCertificateJob;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PurchaseController extends Controller
{

    public function store(Request $request): RedirectResponse
    {
        $plans = config('investment_plans', []);

        $data = $request->validate([
            'plan_key' => ['required', 'string', 'in:'.implode(',', array_keys($plans))],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'payment_method' => ['required', 'string', 'in:account_balance,bank_transfer'],
            'bank_name' => ['required_if:payment_method,bank_transfer', 'string', 'max:255'],
        ]);

        $amountCents = (int) round($data['amount'] * 100);
        $plan = $plans[$data['plan_key']];
        $paymentMethod = $data['payment_method'];
        $bankName = $paymentMethod === 'bank_transfer' ? $data['bank_name'] : null;

        $slotCapacity = $plan['slot_capacity'] ?? null;
        if ($slotCapacity !== null) {
            $availableSlots = $this->getRemainingSlots($data['plan_key']);
            if ($availableSlots <= 0) {
                throw ValidationException::withMessages([
                    'plan_key' => 'This package is sold out.',
                ]);
            }
        }

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
        $now = now();

        DB::transaction(function () use ($user, $amountCents, $plan, $data, $paymentMethod, $bankName, &$purchase, $now): void {
            $userLocked = User::query()->whereKey($user->id)->lockForUpdate()->first();
            $referrerId = null;
            $commissionCents = 0;
            $status = 'pending';

            if ($paymentMethod === 'account_balance') {
                if ($userLocked->balance_cents < $amountCents) {
                    throw ValidationException::withMessages([
                        'amount' => 'Insufficient balance.',
                    ]);
                }

                $userLocked->balance_cents -= $amountCents;
                $userLocked->save();
                $status = 'completed';

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
            } elseif ($paymentMethod === 'bank_transfer' && $userLocked->referrer_id && $userLocked->referrer_id !== $userLocked->id) {
                $referrerId = $userLocked->referrer_id;
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
                'payment_method' => $paymentMethod,
                'bank_name' => $bankName,
                'status' => $status,
            ]);
        });

        \Log::info('Package purchase completed', [
            'purchase_id' => $purchase->id,
            'user_id' => $user->id,
            'plan_name' => $purchase->plan_name,
            'amount_cents' => $purchase->amount_cents,
            'payment_method' => $purchase->payment_method,
            'status' => $purchase->status,
            'daily_interest_bps' => $purchase->daily_interest_bps,
            'duration_days' => $purchase->duration_days,
        ]);

        // Queue certificate generation and sending (async) only for completed purchases
        if ($purchase->status === 'completed') {
            SendCertificateJob::dispatch($user, $purchase);
        }

        $successMessage = $paymentMethod === 'bank_transfer'
            ? 'Bank transfer submitted. We will confirm once payment is received.'
            : 'Purchase completed successfully.';

        // Generate professional receipt
        $dailyInterestPercent = ($purchase->daily_interest_bps / 10000) * 100;
        $totalInterestEstimate = (int) round($purchase->amount_cents * ($dailyInterestPercent / 100) * $purchase->duration_days);
        
        $receipt = [
            'id' => $purchase->id,
            'reference_number' => sprintf('MRC-PKG-%s-%s', $now->format('Ymd'), $purchase->id),
            'plan_name' => $purchase->plan_name,
            'amount_cents' => $purchase->amount_cents,
            'daily_interest_bps' => $purchase->daily_interest_bps,
            'daily_interest_percent' => $dailyInterestPercent,
            'duration_days' => $purchase->duration_days,
            'estimated_interest_cents' => $totalInterestEstimate,
            'payment_method' => $paymentMethod === 'account_balance' ? 'Morrisons Account Balance' : 'Bank Transfer',
            'bank_name' => $bankName,
            'status' => ucfirst($purchase->status),
            'status_badge' => $purchase->status === 'completed' 
                ? '✓ Purchase Successfully Completed' 
                : '⏳ Bank Transfer Pending Confirmation',
            'transaction_date' => $now->format('F j, Y • g:i A'),
            'investor_name' => $user->name,
            'remarks' => $purchase->status === 'completed'
                ? 'Investment successfully processed and verified through the Morrisons secure transaction system. Your investment will begin accruing interest immediately.'
                : 'Bank transfer has been submitted for processing. Your investment will begin accruing interest once payment is confirmed by our banking partners.',
        ];

        if ($purchase->status === 'completed') {
            if (! $user->contract()->exists()) {
                return redirect()->route('contract.index', ['purchase_id' => $purchase->id])
                    ->with([
                        'success' => 'Please complete your investment contract. A copy will be sent to your email.',
                        'purchase_receipt' => $receipt,
                    ]);
            }

            ContractService::sendPurchaseContract($user, $purchase, $user->contract);
        }

        return back()->with([
            'success' => $successMessage,
            'purchase_receipt' => $receipt,
        ]);
    }

    private function getRemainingSlots(string $planKey): int
    {
        return (new PackageSlotService())->getRemainingSlots($planKey);
    }
}
