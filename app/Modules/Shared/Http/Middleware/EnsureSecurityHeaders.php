<?php

declare(strict_types=1);

namespace App\Modules\Shared\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class EnsureSecurityHeaders
{
    private const STYLE_SRC_EXTRA = 'https://fonts.bunny.net';

    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (SymfonyResponse)  $next
     */
    public function handle(Request $request, Closure $next): SymfonyResponse
    {
        /** @var Response $response */
        $response = $next($request);

        // En local, relajar CSP para permitir el dev server de Vite (IPv4 e IPv6)
        $isLocal = app()->environment('local');

        $scriptSrc = "'self' 'unsafe-inline' 'unsafe-eval'";
        $styleSrc = "'self' 'unsafe-inline' ".self::STYLE_SRC_EXTRA;
        $connectSrc = "'self'";

        if ($isLocal) {
            $viteHttp = ['http://localhost:5173', 'http://127.0.0.1:5173'];
            $viteWs = ['ws://localhost:5173', 'ws://127.0.0.1:5173'];
            $scriptSrc .= ' '.implode(' ', $viteHttp);
            $styleSrc .= ' '.implode(' ', $viteHttp);
            $connectSrc .= ' '.implode(' ', array_merge($viteHttp, $viteWs));
        }

        // Allow the app's own domain explicitly (belt-and-suspenders for reverse proxies)
        $appUrl = rtrim((string) config('app.url'), '/');
        $ownOrigin = $appUrl ? " $appUrl" : '';

        // Content-Security-Policy
        $response->headers->set('Content-Security-Policy', implode('; ', [
            "default-src 'self'{$ownOrigin}",
            "script-src {$scriptSrc}{$ownOrigin}",
            "style-src {$styleSrc}{$ownOrigin}",
            "img-src 'self' data:{$ownOrigin}",
            "font-src 'self' https://fonts.bunny.net{$ownOrigin}",
            "connect-src {$connectSrc}{$ownOrigin}",
            "frame-ancestors 'none'",
            "form-action 'self'{$ownOrigin}",
            "base-uri 'self'{$ownOrigin}",
        ]));

        // Strict-Transport-Security (HSTS)
        $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');

        // Referrer-Policy
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // X-Permitted-Cross-Domain-Policies
        $response->headers->set('X-Permitted-Cross-Domain-Policies', 'none');

        // X-Download-Options (IE)
        $response->headers->set('X-Download-Options', 'noopen');

        return $response;
    }
}
