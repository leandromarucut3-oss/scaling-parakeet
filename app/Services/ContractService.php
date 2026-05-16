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
        return Pdf::loadView('contracts.pdf', [
            'user' => $user,
            'purchase' => $purchase,
            'contract' => $contract,
        ])->setPaper('a4')->output();
    }

    public static function sendPurchaseContract(User $user, Purchase $purchase, Contract $contract): void
    {
        $pdf = self::generatePurchaseContractPdf($user, $purchase, $contract);

        Mail::to($user->email)
            ->send(new ContractSigned($user, $purchase, $contract, $pdf));
    }
}
