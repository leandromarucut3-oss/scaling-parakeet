<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class CanvaService
{
    protected $client;
    protected $baseUri;

    public function __construct()
    {
        $this->baseUri = config('services.canva.base_uri', 'https://api.canva.com/');
        $this->client = new Client([
            'base_uri' => $this->baseUri,
            'timeout' => 30,
        ]);
    }

    protected function getAccessToken()
    {
        $cacheKey = 'canva_access_token';
        return Cache::remember($cacheKey, 3500, function () {
            try {
                $resp = $this->client->post('v1/oauth2/token', [
                    'form_params' => [
                        'grant_type' => 'client_credentials',
                        'client_id' => config('services.canva.client_id'),
                        'client_secret' => config('services.canva.client_secret'),
                    ],
                ]);

                $body = json_decode((string) $resp->getBody(), true);
                if (! empty($body['access_token'])) {
                    // Respect expires_in if present
                    $ttl = isset($body['expires_in']) ? intval($body['expires_in']) : 3500;
                    Cache::put($cacheKey, $body, $ttl - 10);
                    return $body;
                }
            } catch (\Throwable $e) {
                Log::error('Canva token error: ' . $e->getMessage());
            }
            return null;
        });
    }

    /**
     * Create a design from a template and export it to PDF (and optionally PNG).
     * Returns [$pdfPath, $pngPath|null] on success, or false on failure.
     */
    public function exportFromTemplate(string $templateId, array $variables, string $saveDir, string $base)
    {
        $tokenBody = $this->getAccessToken();
        if (empty($tokenBody['access_token'])) {
            Log::warning('Canva: no access token available');
            return false;
        }

        $token = $tokenBody['access_token'];

        try {
            // Create a design from the template
            $resp = $this->client->post('v1/designs', [
                'headers' => [
                    'Authorization' => "Bearer {$token}",
                    'Accept' => 'application/json',
                ],
                'json' => [
                    'templateId' => $templateId,
                    'data' => $variables,
                ],
            ]);

            $body = json_decode((string) $resp->getBody(), true);
            $designId = $body['id'] ?? null;
            if (! $designId) {
                Log::warning('Canva create design failed: ' . json_encode($body));
                return false;
            }

            // Request an export (PDF)
            $exportResp = $this->client->post("v1/designs/{$designId}/exports", [
                'headers' => [
                    'Authorization' => "Bearer {$token}",
                    'Accept' => 'application/json',
                ],
                'json' => [
                    'export' => [
                        'format' => 'pdf',
                        'quality' => 'high',
                    ],
                ],
            ]);

            $exportBody = json_decode((string) $exportResp->getBody(), true);
            $exportId = $exportBody['id'] ?? null;
            if (! $exportId) {
                Log::warning('Canva export creation failed: ' . json_encode($exportBody));
                return false;
            }

            // Poll for completion
            $downloadUrl = null;
            $statusBody = null;
            for ($i = 0; $i < 15; $i++) {
                sleep(1);
                $statusResp = $this->client->get("v1/exports/{$exportId}", [
                    'headers' => [
                        'Authorization' => "Bearer {$token}",
                        'Accept' => 'application/json',
                    ],
                ]);
                $statusBody = json_decode((string) $statusResp->getBody(), true);
                if (! empty($statusBody['status']) && $statusBody['status'] === 'completed' && ! empty($statusBody['resultUrl'])) {
                    $downloadUrl = $statusBody['resultUrl'];
                    break;
                }
            }

            if (! $downloadUrl) {
                Log::warning('Canva export did not complete: ' . json_encode($statusBody ?? []));
                return false;
            }

            // Download exported PDF
            $pdfPath = $saveDir . DIRECTORY_SEPARATOR . $base . '.pdf';
            $this->client->get($downloadUrl, [
                'headers' => [
                    'Authorization' => "Bearer {$token}",
                ],
                'sink' => $pdfPath,
            ]);

            return [$pdfPath, null];
        } catch (\Throwable $e) {
            Log::error('Canva export error: ' . $e->getMessage());
            return false;
        }
    }
}
