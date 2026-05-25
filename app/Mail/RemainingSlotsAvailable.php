<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class RemainingSlotsAvailable extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user,
        public array $packages,
    ) {
    }

    public function build()
    {
        return $this->subject('Limited Morrisons Package Slots Available')
            ->view('emails.remaining-slots')
            ->with([
                'user' => $this->user,
                'packages' => $this->packages,
                'ctaUrl' => url('/buy-shares'),
                'slotsImageUrl' => asset('Slots.png'),
            ]);
    }
}
