<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CertificateMail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $package;
    public $pdfPath;
    public $pngPath;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($user, $package, $pdfPath, $pngPath = null)
    {
        $this->user = $user;
        $this->package = $package;
        $this->pdfPath = $pdfPath;
        $this->pngPath = $pngPath;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $subject = 'Your Certificate of Membership';

        $mail = $this->subject($subject)
                    ->view('emails.certificate_email')
                    ->with([
                        'user' => $this->user,
                        'package' => $this->package,
                    ]);

        // Prefer PNG attachment if available, otherwise attach PDF.
        if ($this->pngPath && file_exists($this->pngPath)) {
            $mail->attach($this->pngPath, [
                'as' => basename($this->pngPath),
                'mime' => 'image/png',
            ]);
        } elseif ($this->pdfPath && file_exists($this->pdfPath)) {
            $mail->attach($this->pdfPath, [
                'as' => basename($this->pdfPath),
                'mime' => 'application/pdf',
            ]);
        }

        return $mail;
    }
}
