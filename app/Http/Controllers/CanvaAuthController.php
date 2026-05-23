<?php

namespace App\Http\Controllers;

use App\Services\CanvaService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class CanvaAuthController extends Controller
{
    public function redirect(Request $request)
    {
        // Generate PKCE code verifier and challenge
        $codeVerifier = rtrim(strtr(base64_encode(random_bytes(64)), '+/', '-_'), '=');
        $challenge = rtrim(strtr(base64_encode(hash('sha256', $codeVerifier, true)), '+/', '-_'), '=');

        $request->session()->put('canva_code_verifier', $codeVerifier);

        $clientId = config('services.canva.client_id');
        $redirectUri = env('CANVA_REDIRECT_URI', config('app.url'));

        $params = http_build_query([
            'code_challenge_method' => 'S256',
            'response_type' => 'code',
            'client_id' => $clientId,
            'redirect_uri' => $redirectUri,
            'code_challenge' => $challenge,
        ]);

        $authBase = env('CANVA_AUTH_URI', 'https://www.canva.com');
        $authorizeUrl = rtrim($authBase, '/') . '/api/oauth/authorize?' . $params;

        return redirect()->away($authorizeUrl);
    }

    public function callback(Request $request)
    {
        $code = $request->query('code');
        $error = $request->query('error');

        if ($error) {
            Log::error('Canva auth error callback: '.$error);
            return response('Authorization failed: '.$error, 400);
        }

        if (! $code) {
            return response('Missing code', 400);
        }

        $codeVerifier = $request->session()->pull('canva_code_verifier');
        if (! $codeVerifier) {
            return response('Missing PKCE verifier in session', 400);
        }

        $redirectUri = env('CANVA_REDIRECT_URI', config('app.url'));
        $canva = new CanvaService();
        $result = $canva->exchangeAuthorizationCode($code, $codeVerifier, $redirectUri);

        if ($result) {
            return response('Canva authorized successfully. Tokens stored.', 200);
        }

        return response('Canva token exchange failed. Check logs.', 500);
    }
}
