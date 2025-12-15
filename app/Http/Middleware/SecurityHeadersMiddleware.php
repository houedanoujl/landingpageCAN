<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeadersMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Protection contre le clickjacking
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');

        // Protection XSS
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-XSS-Protection', '1; mode=block');

        // Content Security Policy - ajusté pour Alpine.js, Google Fonts, Leaflet et flagcdn
        $response->headers->set('Content-Security-Policy',
            "default-src 'self'; " .
            "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.jsdelivr.net https://www.googletagmanager.com https://unpkg.com; " .
            "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://unpkg.com; " .
            "font-src 'self' https://fonts.gstatic.com; " .
            "img-src 'self' data: https: https://flagcdn.com https://tile.openstreetmap.org; " .
            "connect-src 'self' https://www.google-analytics.com https://tile.openstreetmap.org; " .
            "frame-ancestors 'self';"
        );

        // Politique de référents
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // Protection contre le sniffing de type MIME
        $response->headers->set('X-Download-Options', 'noopen');

        // Forcer HTTPS en production
        if (app()->environment('production')) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        return $response;
    }
}
