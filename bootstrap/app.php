<?php

use App\Modules\Auth\Middleware\EnsureOnboardingCompleted;
use App\Modules\Organization\Middleware\EnsureOrganizationAccess;
use App\Modules\Organization\Middleware\EnsureRole;
use App\Modules\Shared\Http\Middleware\EnsureSecurityHeaders;
use App\Modules\Shared\Http\Middleware\SetLocaleFromCookie;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'org.access' => EnsureOrganizationAccess::class,
            'org.role' => EnsureRole::class,
            'onboarding' => EnsureOnboardingCompleted::class,
        ]);

        $middleware->append(EnsureSecurityHeaders::class);

        // La cookie 'locale' NO debe encriptarse — la leemos en SetLocaleFromCookie
        // y queremos que sea legible en el frontend si es necesario
        $middleware->encryptCookies(except: [
            'locale',
        ]);

        // Locale — corre DENTRO del grupo web, después de EncryptCookies y StartSession
        $middleware->appendToGroup('web', SetLocaleFromCookie::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Loggear excepciones no capturadas con contexto completo
        $exceptions->report(function (Throwable $e) {
            Log::error('Unhandled exception', [
                'message' => $e->getMessage(),
                'class' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
        });

        // Response JSON para peticiones AJAX/Livewire
        $exceptions->render(function (Throwable $e, Request $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'error' => 'Error interno del servidor',
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        });
    })->create();
