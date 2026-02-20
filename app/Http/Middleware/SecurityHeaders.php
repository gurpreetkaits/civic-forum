<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Prevent clickjacking
        $response->headers->set('X-Frame-Options', 'DENY');

        // Prevent MIME type sniffing
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        // XSS Protection
        $response->headers->set('X-XSS-Protection', '1; mode=block');

        // Referrer Policy
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // Content Security Policy (production only — Vite dev server conflicts with CSP)
        if (!app()->environment('local')) {
            $response->headers->set(
                'Content-Security-Policy',
                implode('; ', [
                    "default-src 'self'",
                    "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://unpkg.com https://sdk.onfido.com",
                    "style-src 'self' 'unsafe-inline' https://fonts.bunny.net https://sdk.onfido.com",
                    "img-src 'self' data: https: blob:",
                    "font-src 'self' data: https://fonts.bunny.net",
                    "connect-src 'self' https://fonts.bunny.net https://sdk.onfido.com https://*.onfido.com",
                    "media-src 'self' blob:",
                    "frame-src 'self' https://sdk.onfido.com",
                    "frame-ancestors 'self' https://*.google.com",
                ])
            );
        }

        // Permissions Policy (camera & microphone needed for Onfido face verification)
        $response->headers->set(
            'Permissions-Policy',
            'camera=(self), microphone=(self), geolocation=()'
        );

        // Strict Transport Security (only in production)
        if (app()->environment('production')) {
            $response->headers->set(
                'Strict-Transport-Security',
                'max-age=31536000; includeSubDomains'
            );
        }

        return $response;
    }
}
