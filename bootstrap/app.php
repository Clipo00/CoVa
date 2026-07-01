<?php

use App\Modules\Auth\Middleware\EnsureApiAccess;
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
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'api.access' => EnsureApiAccess::class,
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
            $context = [
                'message' => $e->getMessage(),
                'class' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ];

            // Only include stack trace in debug mode
            if (config('app.debug')) {
                $context['trace'] = $e->getTraceAsString();
            }

            Log::error('Unhandled exception', $context);
        });

        // Response JSON con formato RFC 7807 para peticiones api/*
        $exceptions->render(function (Throwable $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                // Let the framework handle ValidationException (422) natively
                if ($e instanceof \Illuminate\Validation\ValidationException) {
                    return;
                }

                $status = Response::HTTP_INTERNAL_SERVER_ERROR;
                $title = 'Internal Server Error';
                $detail = $e->getMessage() ?: $title;

                if ($e instanceof HttpExceptionInterface) {
                    $status = $e->getStatusCode();
                    $title = Response::$statusTexts[$status] ?? 'Unknown Error';
                } elseif ($e instanceof \Illuminate\Auth\AuthenticationException) {
                    $status = Response::HTTP_UNAUTHORIZED;
                    $title = 'Unauthorized';
                } elseif ($e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException) {
                    $status = Response::HTTP_NOT_FOUND;
                    $title = 'Not Found';
                    $detail = 'Resource not found.';
                } elseif ($e instanceof \Illuminate\Auth\AuthorizationException) {
                    $status = Response::HTTP_FORBIDDEN;
                    $title = 'Forbidden';
                    $detail = 'Access denied.';
                }

                // En producción, no exponer detalles internos para 500
                if ($status === Response::HTTP_INTERNAL_SERVER_ERROR && app()->isProduction()) {
                    $detail = 'An unexpected error occurred.';
                }

                return response()->json([
                    'type' => config('app.url') . "/errors/{$status}",
                    'title' => $title,
                    'status' => $status,
                    'detail' => $detail,
                ], $status);
            }
        });
    })->create();
