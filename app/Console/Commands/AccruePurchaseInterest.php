<?php

namespace App\Console\Commands;

use App\Models\Purchase;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class AccruePurchaseInterest extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'purchases:accrue-interest';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Accrue daily interest for active purchases.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $today = now()->startOfDay();

        Purchase::query()
            ->where('status', 'completed')
            ->whereColumn('accrued_days', '<', 'duration_days')
            ->orderBy('id')
            ->chunkById(100, function ($purchases) use ($today): void {
                foreach ($purchases as $purchase) {
                    $purchaseDate = optional($purchase->created_at)->startOfDay();
                    if (! $purchaseDate) {
                        continue;
                    }

                    $daysEligible = $purchaseDate->diffInDays($today, false);
                    if ($daysEligible <= 0) {
                        continue;
                    }

                    $remainingEligible = $daysEligible - $purchase->accrued_days;
                    $remainingDuration = $purchase->duration_days - $purchase->accrued_days;

                    if ($remainingEligible <= 0 || $remainingDuration <= 0) {
                        continue;
                    }

                    $daysToAccrue = min($remainingEligible, $remainingDuration);
                    $dailyInterestCents = intdiv($purchase->amount_cents * $purchase->daily_interest_bps, 10000);

                    if ($dailyInterestCents <= 0) {
                        continue;
                    }

                    $totalInterestCents = $dailyInterestCents * $daysToAccrue;

                    DB::transaction(function () use ($purchase, $totalInterestCents, $daysToAccrue, $today): void {
                        $purchaseLocked = Purchase::query()->whereKey($purchase->id)->lockForUpdate()->first();
                        $userLocked = User::query()->whereKey($purchase->user_id)->lockForUpdate()->first();

                        if (! $purchaseLocked || ! $userLocked) {
                            return;
                        }

                        $purchaseLocked->interest_earned_cents += $totalInterestCents;
                        $purchaseLocked->accrued_days += $daysToAccrue;
                        $purchaseLocked->last_interest_at = $today;
                        $purchaseLocked->save();

                        $userLocked->balance_cents += $totalInterestCents;
                        $userLocked->save();
                    });
                }
            });

        return self::SUCCESS;
    }
}
