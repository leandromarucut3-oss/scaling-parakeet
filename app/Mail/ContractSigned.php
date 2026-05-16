<?php

namespace App\Mail;

use App\Models\Contract;
use App\Models\Purchase;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ContractSigned extends Mailable
{
    use Queueable, SerializesModels;

    public User $user;
    public Purchase $purchase;
    public Contract $contract;
    public string $pdf;

    public function __construct(User $user, Purchase $purchase, Contract $contract, string $pdf)
    {
        $this->user = $user;
        $this->purchase = $purchase;
        $this->contract = $contract;
        $this->pdf = $pdf;
    }

    public function build()
    {
        return $this->subject('Your Investment Contract')
            ->view('emails.contract-signed')
            ->attachData($this->pdf, 'investment-contract.pdf', [
                'mime' => 'application/pdf',
            ]);
    }
}
