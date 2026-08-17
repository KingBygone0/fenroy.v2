<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Foundation\Vite;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        // Generate a fresh nonce for each request and register it with Vite.
        // Livewire reads Vite::cspNonce() automatically for its injected scripts.
        $nonce = base64_encode(Str::random(16));
        app(Vite::class)->useCspNonce($nonce);

        $response = $next($request);

        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set(
            'Permissions-Policy',
            'geolocation=(), microphone=(), camera=(), payment=(), usb=(), magnetometer=(), accelerometer=(), gyroscope=()'
        );
        $response->headers->set('X-Permitted-Cross-Domain-Policies', 'none');

        // HSTS — only meaningful over HTTPS; send always so it's cached on first HTTPS response
        if ($request->secure()) {
            $response->headers->set(
                'Strict-Transport-Security',
                'max-age=31536000; includeSubDomains; preload'
            );
        }

        $response->headers->set('Content-Security-Policy', $this->buildCsp($nonce));

        return $response;
    }

    private function buildCsp(string $nonce): string
    {
        // Nonce applied to script-src: browsers ignore 'unsafe-inline' when a nonce is present,
        // so all inline <script> blocks must carry nonce="{{ Vite::cspNonce() }}" in views.
        // 'unsafe-eval' remains: Alpine.js default build uses new Function() for expression parsing.
        //   Switching to @alpinejs/csp build removes this but requires all x-data to be named components.
        // style-src includes Google Fonts and Paystack button stylesheet.
        // frame-src includes both js.paystack.co (script host) and checkout.paystack.com (popup frame).
        return implode('; ', [
            "default-src 'self'",
            "script-src 'self' 'nonce-{$nonce}' 'unsafe-eval' https://js.paystack.co https://www.googletagmanager.com https://www.google-analytics.com https://region1.google-analytics.com",
            "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://paystack.com",
            "img-src 'self' data: https:",
            "connect-src 'self' https://www.google-analytics.com https://region1.google-analytics.com https://analytics.google.com https://api.paystack.co https://js.paystack.co https://checkout.paystack.com https://fonts.gstatic.com https://fonts.googleapis.com https://eu.i.posthog.com https://eu-assets.i.posthog.com",
            "frame-src 'self' https://js.paystack.co https://checkout.paystack.com",
            "font-src 'self' data: https://fonts.gstatic.com",
            "object-src 'none'",
            "base-uri 'self'",
            "form-action 'self'",
            "frame-ancestors 'self'",
            "upgrade-insecure-requests",
        ]);
    }
}
