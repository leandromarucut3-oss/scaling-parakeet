<?php

namespace Tests\Feature;

use App\Models\Contract;
use App\Models\Purchase;
use App\Models\User;
use Carbon\Carbon;
use Tests\TestCase;

class ContractPdfDateTest extends TestCase
{
    public function test_contract_pdf_uses_purchase_completion_date_instead_of_reused_contract_signed_date(): void
    {
        config(['app.timezone' => 'Asia/Manila']);

        $user = new User([
            'name' => 'Test Investor',
            'email' => 'investor@example.com',
        ]);

        $contract = new Contract([
            'investor_name' => 'Test Investor',
            'civil_status' => 'Single',
            'complete_address' => 'Taguig City',
            'id_type' => 'Passport',
            'id_number' => 'A1234567',
            'id_date_issued' => '2026-01-15',
            'signature_text' => 'Test Investor',
            'signed_at' => Carbon::parse('2026-05-23 10:00:00', 'Asia/Manila'),
        ]);

        $purchase = new Purchase([
            'plan_name' => 'Premiere Plan',
            'duration_days' => 120,
            'amount_cents' => 100000,
            'daily_interest_bps' => 100,
        ]);
        $purchase->created_at = Carbon::parse('2027-06-14 18:00:00', 'Asia/Manila');
        $purchase->updated_at = Carbon::parse('2027-06-15 09:30:00', 'Asia/Manila');

        $html = view('contracts.pdf', [
            'user' => $user,
            'purchase' => $purchase,
            'contract' => $contract,
            'contractDate' => $purchase->updated_at,
            'usdToPhpRate' => 55.0,
            'usdAmount' => 1000,
            'phpAmount' => 55000,
            'dailyInterestUsd' => 10,
            'dailyInterestPhp' => 550,
        ])->render();

        $this->assertStringContainsString('June 15, 2027', $html);
        $this->assertStringContainsString('Series of 2027', $html);
        $this->assertStringNotContainsString('May 23, 2026', $html);
    }
}
