<?php

namespace App\Services;

use App\Mail\ContractSigned;
use App\Models\Contract;
use App\Models\Purchase;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Barryvdh\DomPDF\Facade\Pdf;

class ContractService
{
    public static function generatePurchaseContractPdf(User $user, Purchase $purchase, Contract $contract): string
    {
        $usdToPhpRate = (float) env('USD_TO_PHP_RATE', 55.0);
        $usdAmount = $purchase->amount_cents / 100;
        $phpAmount = $usdAmount * $usdToPhpRate;
        $dailyInterestUsd = $usdAmount * ($purchase->daily_interest_bps / 10000);
        $dailyInterestPhp = $dailyInterestUsd * $usdToPhpRate;

        return Pdf::loadView('contracts.pdf', [
            'user' => $user,
            'purchase' => $purchase,
            'contract' => $contract,
            'usdToPhpRate' => $usdToPhpRate,
            'usdAmount' => $usdAmount,
            'phpAmount' => $phpAmount,
            'dailyInterestUsd' => $dailyInterestUsd,
            'dailyInterestPhp' => $dailyInterestPhp,
        ])->setPaper('a4')->output();
    }

    public static function sendPurchaseContract(User $user, Purchase $purchase, Contract $contract): void
    {
        $pdf = self::generatePurchaseContractPdf($user, $purchase, $contract);

        Mail::to($user->email)
            ->send(new ContractSigned($user, $purchase, $contract, $pdf));
    }
}
