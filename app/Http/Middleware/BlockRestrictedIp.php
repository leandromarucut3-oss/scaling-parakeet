<?php

namespace App\Http\Middleware;

use App\Models\BlockedIp;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class BlockRestrictedIp
{
    public function handle(Request $request, Closure $next): Response
    {
        $ipAddress = $request->ip();

        if ($ipAddress && BlockedIp::query()->where('ip_address', $ipAddress)->exists()) {
            abort(403, 'Access to this website has been restricted.');
        }

        return $next($request);
    }
}
