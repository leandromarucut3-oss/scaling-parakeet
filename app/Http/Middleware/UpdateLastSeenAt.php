<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class UpdateLastSeenAt
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check()) {
            $user = auth()->user();
            $ipAddress = $request->ip();

            // Only update if last_seen_at is null or more than 1 minute old
            if (!$user->last_seen_at || $user->last_seen_at->addMinute()->isPast() || $user->last_ip_address !== $ipAddress) {
                $user->update([
                    'last_seen_at' => now(),
                    'last_ip_address' => $ipAddress,
                ]);
            }
        }

        return $next($request);
    }
}
