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

        // Content Security Policy - ajusté pour Alpine.js, Google Fonts, Leaflet, OpenStreetMap, Swiper, flagcdn, Livescore widget et vidéos
        //
        // NOTE: 'unsafe-inline' / 'unsafe-eval' restent nécessaires car Alpine.js et les
        // scripts inline des vues en dépendent. Pour les supprimer il faudrait migrer
        // tous les scripts inline vers des nonces par requête (refacto transverse des vues).
        // En attendant, on durcit ce qui peut l'être sans rien casser : object-src,
        // base-uri, form-action et la montée en HTTPS forcée en production.
        $csp =
            "default-src 'self'; " .
            "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com https://www.googletagmanager.com https://unpkg.com https://ls.soccersapi.com https://www.youtube.com https://www.facebook.com https://www.tiktok.com; " .
            "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://cdn.jsdelivr.net https://cdnjs.cloudflare.com https://unpkg.com https://ls.soccersapi.com; " .
            "font-src 'self' data: https://fonts.gstatic.com; " .
            "img-src 'self' data: https: blob: https://flagcdn.com https://tile.openstreetmap.org https://www.googletagmanager.com https://ls.soccersapi.com https://img.youtube.com https://i.ytimg.com; " .
            "connect-src 'self' https://cdn.jsdelivr.net https://www.google-analytics.com https://analytics.google.com https://region1.google-analytics.com https://tile.openstreetmap.org https://nominatim.openstreetmap.org https://ls.soccersapi.com https://unpkg.com; " .
            "frame-src 'self' https://ls.soccersapi.com https://www.youtube.com https://youtube.com https://www.youtube-nocookie.com https://www.facebook.com https://facebook.com https://www.tiktok.com https://tiktok.com https://lookerstudio.google.com; " .
            "frame-ancestors 'self'; " .
            "object-src 'none'; " .
            "base-uri 'self'; " .
            "form-action 'self';";

        // Forcer la résolution en HTTPS de toutes les ressources en production.
        if (app()->environment('production')) {
            $csp .= " upgrade-insecure-requests;";
        }

        $response->headers->set('Content-Security-Policy', $csp);

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
