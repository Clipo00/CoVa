<?php

namespace App\Modules\Shared\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocaleFromCookie
{
    private const SUPPORTED_LOCALES = ['es', 'en'];

    /**
     * Handle an incoming request.
     *
     * Orden de precedencia para determinar el locale:
     * 1. Usuario autenticado con locale en BD
     * 2. Cookie 'locale' (para invitados o usuarios sin preferencia guardada)
     * 3. Config default (es)
     *
     * Corre DENTRO del grupo web, después de EncryptCookies y StartSession.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $locale = null;

        // 1. Usuario autenticado → priorizar su preferencia en BD
        if ($request->user()?->locale) {
            $locale = $request->user()->locale;
        }

        // 2. Fallback a cookie (invitados o usuarios sin locale en BD)
        if (!$locale) {
            $locale = $request->cookie('locale');
        }

        // 3. Default de configuración
        if (!$locale || !in_array($locale, self::SUPPORTED_LOCALES, true)) {
            $locale = config('app.locale', 'es');
        }

        app()->setLocale($locale);

        return $next($request);
    }
}
