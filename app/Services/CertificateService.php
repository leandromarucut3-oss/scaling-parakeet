<?php

namespace App\Services;

use App\Mail\CertificateMail;
use Illuminate\Support\Facades\Mail;

class CertificateService
{
    /**
     * Generate a certificate PDF from a Blade view and email it to the user.
     *
     * @param  \App\Models\User  $user
     * @param  array|object $package  // package details (name, price, etc.)
     * @return array [pdfPath, pngPath or null]
     */
    public static function generateAndSend($user, $package)
    {
        // Prepare data for the certificate template
        $data = [
            'user' => $user,
            'package' => $package,
            'issued_at' => now()->toFormattedDateString(),
            'certificate_id' => 'CERT-'.strtoupper(uniqid()),
        ];

        // Ensure directory exists
        $dir = storage_path('app/certificates');
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $base = 'certificate_'.($user->id ?? 'user').'_'.time();
        $pdfPath = $dir.DIRECTORY_SEPARATOR.$base.'.pdf';
        $pngPath = $dir.DIRECTORY_SEPARATOR.$base.'.png';

        // Render Blade to HTML and write to temporary file
        $html = view('certificates.membership', $data)->render();
        $tmpHtml = $dir.DIRECTORY_SEPARATOR.$base.'.html';
        file_put_contents($tmpHtml, $html);

        // Try Puppeteer renderer (node script)
        $nodeRenderer = base_path('tools/certificate-renderer/renderer.js');
        $nodeExecutable = env('CERT_RENDERER_NODE', 'node');
        $usedPuppeteer = false;

        if (file_exists($nodeRenderer)) {
            $cmd = sprintf('%s %s %s %s 2>&1',
                escapeshellcmd($nodeExecutable),
                escapeshellarg($nodeRenderer),
                escapeshellarg($tmpHtml),
                escapeshellarg($pdfPath).' '.escapeshellarg($pngPath)
            );

            // run command
            try {
                $output = [];
                $returnVar = 0;
                exec($cmd, $output, $returnVar);
                if ($returnVar === 0 && file_exists($pdfPath)) {
                    $usedPuppeteer = true;
                } else {
                    \Log::warning('Puppeteer renderer failed: '.implode("\n", $output));
                }
            } catch (\Throwable $e) {
                \Log::warning('Puppeteer renderer execution error: '.$e->getMessage());
            }
        }

        // If puppeteer didn't produce files, fall back to DomPDF (PDF) and Imagick (PNG)
        if (! $usedPuppeteer) {
            try {
                $pdf = \Barryvdh\DomPDF\Facades\Pdf::loadView('certificates.membership', $data);
                $pdf->setPaper('a4', 'landscape');
                $pdf->save($pdfPath);
            } catch (\Throwable $e) {
                \Log::error('DomPDF certificate generation failed: '.$e->getMessage());
            }

            if (class_exists('\\Imagick') && file_exists($pdfPath)) {
                try {
                    $imagick = new \Imagick();
                    $imagick->setResolution(300, 300);
                    $imagick->readImage($pdfPath);
                    $imagick->setImageBackgroundColor('white');
                    $imagick = $imagick->mergeImageLayers(\Imagick::LAYERMETHOD_FLATTEN);
                    $imagick->setImageFormat('png24');
                    $imagick->writeImage($pngPath);
                    $imagick->clear();
                    $imagick->destroy();
                } catch (\Throwable $e) {
                    \Log::warning('Imagick conversion failed: '.$e->getMessage());
                }
            }
        }

        // Email the pdf and png (if available)
        Mail::to($user->email)->send(new CertificateMail($user, $package, $pdfPath, file_exists($pngPath) ? $pngPath : null));

        // Cleanup tmp html
        if (file_exists($tmpHtml)) {
            @unlink($tmpHtml);
        }

        return [$pdfPath, file_exists($pngPath) ? $pngPath : null];
    }
}
