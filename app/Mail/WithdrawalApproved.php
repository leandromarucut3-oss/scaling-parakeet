<?php

namespace App\Mail;

use App\Models\WithdrawalRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class WithdrawalApproved extends Mailable
{
    use Queueable, SerializesModels;

    public WithdrawalRequest $withdrawal;

    public function __construct(WithdrawalRequest $withdrawal)
    {
        $this->withdrawal = $withdrawal;
    }

    public function build()
    {
        return $this->subject('Withdrawal Request Processed')
            ->view('emails.withdrawal-processed')
            ->with([
                'withdrawal' => $this->withdrawal,
                'user' => $this->withdrawal->user,
            ]);
    }
}
