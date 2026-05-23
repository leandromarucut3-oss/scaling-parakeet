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

        // Attach a neutral, user-facing file name and avoid exposing any Canva URLs or filenames.
        // Preferred behavior: deliver a PNG named 'Certificate.png'. Convert when possible.
        $attachPath = null;
        $attachMime = null;
        $attachName = null;

        // If we already have a PNG file from renderers, prefer it.
        if ($this->pngPath && file_exists($this->pngPath)) {
            $attachPath = $this->pngPath;
            $attachMime = 'image/png';
            $attachName = 'Certificate.png';
        }

        // If no PNG, but we have a PDF, try to convert first page to PNG if Imagick is available.
        if (! $attachPath && $this->pdfPath && file_exists($this->pdfPath)) {
            if (class_exists('\\Imagick')) {
                try {
                    $im = new \Imagick();
                    $im->setResolution(300, 300);
                    $im->readImage($this->pdfPath . '[0]'); // first page only
                    $im->setImageBackgroundColor('white');
                    $im = $im->mergeImageLayers(\Imagick::LAYERMETHOD_FLATTEN);
                    $im->setImageFormat('png24');
                    $tmpPng = sys_get_temp_dir().DIRECTORY_SEPARATOR.'certificate_'.uniqid().'.png';
                    $im->writeImage($tmpPng);
                    $im->clear();
                    $im->destroy();
                    if (file_exists($tmpPng)) {
                        $attachPath = $tmpPng;
                        $attachMime = 'image/png';
                        $attachName = 'Certificate.png';
                    }
                } catch (\Throwable $e) {
                    // conversion failed; fall back to attaching PDF (as last resort)
                    $attachPath = $this->pdfPath;
                    $attachMime = 'application/pdf';
                    $attachName = 'Certificate.pdf';
                }
            } else {
                // No Imagick available: fall back to attaching PDF
                $attachPath = $this->pdfPath;
                $attachMime = 'application/pdf';
                $attachName = 'Certificate.pdf';
            }
        }

        // Finally attach if we have something
        if ($attachPath && file_exists($attachPath)) {
            $mail->attach($attachPath, [
                'as' => $attachName ?? basename($attachPath),
                'mime' => $attachMime ?? mime_content_type($attachPath),
            ]);
        }

        return $mail;
    }
}
