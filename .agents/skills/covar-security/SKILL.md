---
name: covar-security
description: >
  Patrones de seguridad OWASP Top 10:2025 para CoVa (Laravel + Livewire + Alpine.js).
  Trigger: Siempre que se crea o modifica código en el proyecto.
license: Apache-2.0
metadata:
  author: gentleman-programming
  version: "1.0"
---

## When to Use

- **SIEMPRE** — toda modificación de código en CoVa debe considerar implicaciones de seguridad
- Creando o editando: Controllers, Models, Policies, Livewire, Blade, Routes, Middleware
- Configurando: sesiones, CORS, CSP, headers HTTP, manejo de errores
- Modificando: autenticación, registro, invitaciones, roles
- Exponiendo: APIs, webhooks, datos de usuarios

## OWASP Top 10:2025 — Quick Reference

| # | Categoría | Riesgo en CoVa | Prioridad |
|---|-----------|----------------|-----------|
| A01 | Broken Access Control | Policies, rutas, parámetros GET | 🔴 Alta |
| A02 | Security Misconfiguration | .env, headers, CORS, APP_DEBUG | 🔴 Alta |
| A03 | Supply Chain Failures | Composer, npm, CI/CD | 🟡 Media |
| A04 | Cryptographic Failures | Hashing, HTTPS, encrypt | 🔴 Alta |
| A05 | Injection | XSS en Blade, SQL en queries raw | 🔴 Alta |
| A06 | Insecure Design | Validación, rate limiting, lógica | 🟡 Media |
| A07 | Authentication Failures | Login, sesiones, MFA, brute force | 🔴 Alta |
| A08 | Integrity Failures | CSP, signed URLs, webhooks | 🟡 Media |
| A09 | Logging Failures | Auditoría, failed logins, monitoring | 🟢 Baja |
| A10 | Exception Mishandling | Errores 500, stack traces, debug output | 🔴 Alta |

---

## A01: Broken Access Control

### Anti-patterns ❌

```php
// ❌ ID auto-incremental en URL GET (OWASP A01)
<a href="{{ route('blueprints.index', ['org' => $organization->id]) }}">

// ❌ No verificar rol antes de eliminar
public function destroy($id) { Model::destroy($id); }

// ❌ Confiar en que el usuario no manipulará IDs en POST
$blueprint = Blueprint::findOrFail(request('blueprint_id'));
```

### Patrón correcto ✅

```php
// ✅ Slug en URLs, ID solo en POST (con auth)
<a href="{{ route('blueprints.index', ['org' => $organization->slug]) }}">

// ✅ Policy-based authorization en Controller
public function destroy(string $uuid): RedirectResponse
{
    $blueprint = Blueprint::where('uuid', $uuid)->firstOrFail();
    $this->authorize('delete', $blueprint);
    // ...
}

// ✅ Verificar membresía antes de cualquier operación
$membership = $user->membershipIn($organization);
if (!$membership || $membership->role !== OrganizationUser::ROLE_OWNER) {
    abort(403);
}
```

### Reglas CoVa
1. **Slug/UUID en URLs, NUNCA IDs auto-incrementales**
2. **Siempre Policy** — `$this->authorize()` en Controller, `@can()` en Blade
3. **Verificar membresía primero** — si `membershipIn()` es null, deny
4. **Livewire**: proteger con `authorize()` en métodos del componente
5. **OWASP A01 check**: buscar `?org=` o `?user_id=` en parámetros GET

---

## A02: Security Misconfiguration

### Anti-patterns ❌

```php
// ❌ APP_DEBUG=true en producción .env
// ❌ SESSION_ENCRYPT=false para datos sensibles
// ❌ Sin CSP headers
// ❌ CORS demasiado permisivo ('*')
```

### Patrón correcto ✅

```php
// .env.production
APP_DEBUG=false
APP_ENV=production
SESSION_ENCRYPT=true          // ← CoVa tiene SESSION_ENCRYPT=false por defecto
SESSION_SECURE_COOKIE=true
SESSION_HTTP_ONLY=true
SESSION_SAME_SITE=lax

// config/sanctum.php — stateful con dominio exacto, no comodín
'stateful' => [
    parse_url(env('APP_URL'), PHP_URL_HOST),
],
```

### Reglas CoVa
1. **NUNCA** commit `.env` con `APP_DEBUG=true` o `APP_KEY=` vacío
2. **Headers de seguridad**: Laravel ya incluye X-Frame-Options y X-Content-Type-Options. NO REMOVER.
3. **CORS**: usar `allowed_origins` específicos, NUNCA `['*']`
4. **Session**: evaluar si `SESSION_ENCRYPT=true` es necesario por tipo de datos
5. **Verificar**: `php artisan config:clear` antes de deploy

---

## A03: Software Supply Chain Failures

### Anti-patterns ❌

```bash
# ❌ composer install --no-audit sin verificar dependencias
# ❌ package.json con versiones sueltas (sin lockfile)
# ❌ Dependencias sin actually known vulnerabilities (AKV) check
```

### Patrón correcto ✅

```bash
# ✅ Verificar vulnerabilidades conocidas
composer audit

# ✅ npm audit para frontend
npm audit

# ✅ Mantener lockfiles en VCS (composer.lock, package-lock.json)
# ✅ Usar versiones exactas o de rango seguro
"laravel/framework": "^13.0"
```

### Reglas CoVa
1. **Siempre** ejecutar `composer audit` antes de commits que agreguen/quiten dependencias
2. **NUNCA** ignorar lockfiles (`.gitignore` no debe tener `composer.lock` ni `package-lock.json`)
3. **Dependencias dev** separadas en `require-dev`
4. **Evaluar cada dependencia** nueva — ¿es mantenida? ¿tiene historial de vulnerabilidades?

---

## A04: Cryptographic Failures

### Anti-patterns ❌

```php
// ❌ Usar MD5/SHA1 para passwords
hash('md5', $password);

// ❌ Almacenar datos sensibles en texto plano en DB
// ❌ HTTP en vez de HTTPS
// ❌ SESSION_ENCRYPT=false con datos de usuario sensibles
```

### Patrón correcto ✅

```php
// ✅ Password hasher wrapper (ya existe en CoVa)
$hasher = new PasswordHasher();
$hash = $hasher->hash($password);
$verified = $hasher->verify($password, $hash);

// ✅ Encriptar datos sensibles
use Illuminate\Support\Facades\Crypt;

public function storeSensitiveData(string $value): string
{
    return Crypt::encryptString($value);
}

// ✅ Usar bcrypt default de Laravel
'config/hashing.php' => [
    'driver' => 'bcrypt',
    'rounds' => env('BCRYPT_ROUNDS', 12),
]
```

### Reglas CoVa
1. **NUNCA** almacenar passwords sin hash — Laravel ya lo hace, NO hacerlo manual
2. **bcrypt con rounds >= 12** para password hashing
3. **Forzar HTTPS** en producción — configurar `TrustProxies` o `APP_URL=https://`
4. **Datos sensibles**: evaluar si necesitan `Crypt::encryptString()` en DB
5. **Value Object `Email`**: lowercase automático, validación en constructor

---

## A05: Injection

### Anti-patterns ❌

```blade
{{-- ❌ XSS: usar {!! !!} con datos de usuario sin escapar --}}
{!! $userInput !!}

{{-- ❌ HTML sin escapar en Alpine --}}
<div x-html="userInput"></div>
```

```php
// ❌ SQL injection (raw queries sin bindings)
DB::select("SELECT * FROM blueprints WHERE slug = '$slug'");

// ❌ Command injection
shell_exec("echo " . $userInput);
```

### Patrón correcto ✅

```blade
{{-- ✅ Blade escapa automáticamente con {{ }} --}}
<p>{{ $blueprint->title }}</p>

{{-- ✅ Si necesitas HTML, sanitizar con e() o strip_tags --}}
{!! strip_tags($blueprint->description, '<br><strong><em>') !!}

{{-- ✅ Alpine: usar x-text para texto plano --}}
<div x-text="blueprintTitle"></div>
```

```php
// ✅ Eloquent (SQL injection safe)
Blueprint::where('slug', $slug)->first();

// ✅ DB raw con bindings
DB::select("SELECT * FROM blueprints WHERE slug = ?", [$slug]);

// ✅ Validar input siempre
$validated = $request->validate([
    'title' => 'required|string|max:255',
    'slug' => 'required|string|regex:/^[a-z0-9-]+$/',
]);
```

### Reglas CoVa
1. **NUNCA** usar `{!! !!}` con datos de usuario sin sanitizar — LARAVEL NO ESCAPA
2. **Siempre** usar `{{ }}` (doble llave) en Blade para datos de usuario
3. **Alpine**: preferir `x-text` sobre `x-html`; si necesitas `x-html`, sanitizar antes
4. **Eloquent**: es safe por defecto. Raw queries: usar bindings (`?` o `:name`)
5. **Command injection**: evitar `shell_exec`, `exec`, `system`. Si es inevitable, escapar con `escapeshellarg()`
6. **Validar slugs**: solo lowercase, números y guiones — `regex:/^[a-z0-9-]+$/`

---

## A06: Insecure Design

### Anti-patterns ❌

```php
// ❌ Sin rate limiting en endpoints sensibles
Route::post('/blueprints', [BlueprintController::class, 'store']);

// ❌ Validación solo en frontend
// ❌ No verificar límites del plan antes de crear
// ❌ Confiar en IDs que vienen del cliente
```

### Patrón correcto ✅

```php
// ✅ Rate limiting en rutas sensibles
Route::middleware(['throttle:10,1'])->group(function () {
    Route::post('/blueprints', [BlueprintController::class, 'store']);
});

// ✅ Validación server-side + límites del plan (Actions)
class CreateBlueprint
{
    public function execute(Organization $organization, string $title, string $slug): Blueprint
    {
        // Verificar límite del plan
        if ($organization->hasReachedBlueprintLimit()) {
            throw new MaxBlueprintsReachedException();
        }
        // ...
    }
}

// ✅ Validación en Livewire Form
class BlueprintCreateForm extends Form
{
    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'slug' => 'required|string|regex:/^[a-z0-9-]+$/|unique:blueprints,slug',
        ];
    }
}
```

### Reglas CoVa
1. **Rate limiting** en: login, register, blueprint create/update, invitaciones
2. **Validación SIEMPRE server-side** — frontend es decorativo
3. **Límites de plan** validados en Actions, no en Controllers
4. **Business logic** en Actions, no en Livewire ni Controllers
5. **NUNCA confiar en IDs** del cliente para autorización — resolver por slug/UUID y verificar ownership

---

## A07: Authentication Failures

### Anti-patterns ❌

```php
// ❌ Sesión sin timeout configurado
// ❌ Sin rate limit en login
// ❌ No registrar intentos fallidos
// ❌ Permitir passwords débiles
// ❌ Sin logout de otras sesiones al cambiar password
```

### Patrón correcto ✅

```php
// ✅ Session config segura en producción
SESSION_LIFETIME=120           // minutos (2h)
SESSION_ENCRYPT=true           // proteger datos de sesión

// ✅ Rate limiting en login (Laravel built-in)
use Illuminate\Validation\ValidationException;

public function login(Request $request): RedirectResponse
{
    $request->validate([
        'email' => 'required|email',
        'password' => 'required|string',
    ]);

    // Rate limiter built-in de Laravel
    if ($this->hasTooManyLoginAttempts($request)) {
        $this->fireLockoutEvent($request);
        throw ValidationException::withMessages([
            'email' => __('auth.throttle'),
        ]);
    }
    // ...
}

// ✅ Regenerar sesión después de login
session()->regenerate();

// ✅ Invalidar sesiones al cambiar password
auth()->user()->update(['password' => bcrypt($newPassword)]);
// Marcar todas las sesiones como inválidas
auth()->logoutOtherDevices($newPassword);
```

### Reglas CoVa
1. **Laravel ya maneja** login seguro, session regeneration, CSRF — NO DESHABILITAR
2. **Rate limiting**: rutas de auth deben tener `throttle`
3. **Password rules**: min 8 chars, mix de caracteres
4. **Recordar**: invalidar sesiones al cambiar password con `logoutOtherDevices()`
5. **Sanctum**: si se expone API, usar tokens con expiración y scopes
6. **Password reset**: usar el built-in de Laravel con tokens expirables

---

## A08: Software or Data Integrity Failures

### Anti-patterns ❌

```php
// ❌ Sin CSP headers
// ❌ Webhooks sin verificación de firma
// ❌ URLs sin firmar para acciones sensibles
// ❌ Actualizaciones automáticas sin verificar checksums
```

### Patrón correcto ✅

```php
// ✅ Signed URLs para acciones sensibles por email
use Illuminate\Support\Facades\URL;

$signedUrl = URL::temporarySignedRoute(
    'invitations.accept',
    now()->addDays(7),
    ['invitation' => $invitation->id]
);

// ✅ Verificar signed URLs en el controller
public function accept(Request $request): RedirectResponse
{
    if (!$request->hasValidSignature()) {
        abort(401, 'Enlace de invitación inválido o expirado.');
    }
    // ...
}

// ✅ CSP vía middleware (pendiente en CoVa — implementar)
// Middleware sugerido:
namespace App\Http\Middleware;

class ContentSecurityPolicy
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);
        $response->headers->set('Content-Security-Policy', 
            "default-src 'self'; " .
            "script-src 'self' 'unsafe-inline' 'unsafe-eval'; " .
            "style-src 'self' 'unsafe-inline'; " .
            "img-src 'self' data:;"
        );
        return $response;
    }
}
```

### Reglas CoVa
1. **CSP**: CoVa NO tiene CSP implementado — es prioritario para producción
2. **Signed URLs**: usar para invitaciones, password reset, confirmaciones por email
3. **Webhooks**: si se implementan, verificar firma HMAC
4. **Asset integrity**: Vite ya maneja hashing de assets, no deshabilitar

---

## A09: Security Logging and Alerting Failures

### Anti-patterns ❌

```php
// ❌ No registrar intentos de login fallidos
// ❌ No loguear accesos no autorizados (403)
// ❌ Sin monitoreo de actividad sospechosa
```

### Patrón correcto ✅

```php
// ✅ Logging de eventos de seguridad
use Illuminate\Support\Facades\Log;

// En Action/Controller
public function login(Request $request): RedirectResponse
{
    // ...
    if (!$authenticated) {
        Log::warning('Intento de login fallido', [
            'email' => $request->email,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);
        // ...
    }

    Log::info('Login exitoso', [
        'user_id' => $user->id,
        'ip' => $request->ip(),
    ]);
}

// ✅ Logging de autorización fallida
$this->authorize('delete', $blueprint); // Si falla, Laravel ya loguea 403

// ✅ Log contextualizado
Log::channel('audit')->info('Blueprint eliminado', [
    'blueprint_uuid' => $blueprint->uuid,
    'organization_id' => $organization->id,
    'deleted_by' => $user->id,
]);
```

### Reglas CoVa
1. **Loggear intentos fallidos** de login (email, IP, user agent)
2. **Loggear acciones destructivas** (eliminar/restore blueprints, eliminar orgs, remover miembros)
3. **Canales separados**: considerar `Log::channel('audit')` para eventos de seguridad
4. **NO loggear** datos sensibles (passwords, tokens, secrets)
5. **Laravel ya loguea** 403/404 automáticamente

---

## A10: Mishandling of Exceptional Conditions

### Anti-patterns ❌

```blade
{{-- ❌ Stack traces expuestos al usuario --}}
@if(app()->isLocal())
    <pre>{{ $exception->getTraceAsString() }}</pre>
@endif
```

```php
// ❌ Mensajes de error que revelan información interna
abort(500, 'MySQL connection failed on query: SELECT ...');
```

### Patrón correcto ✅

```php
// ✅ Páginas de error custom (NO stack traces)
// resources/views/errors/403.blade.php
// resources/views/errors/404.blade.php
// resources/views/errors/500.blade.php

// ✅ Exception handler en bootstrap/app.php
->withExceptions(function (Exceptions $exceptions): void {
    // Personalizar respuesta 500
    $exceptions->render(function (Throwable $e, Request $request) {
        if ($request->expectsJson()) {
            return response()->json([
                'error' => 'Error interno del servidor',
            ], 500);
        }

        // Loggear el error real
        Log::error('Unhandled exception', [
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
        ]);

        // Mostrar página amigable
        return response()->view('errors.500', [], 500);
    });
})

// ✅ Excepciones custom de dominio (sin info sensible)
class MaxBlueprintsReachedException extends \RuntimeException
{
    public function __construct()
    {
        parent::__construct('Límite de blueprints alcanzado para esta organización.');
    }
}
```

### Reglas CoVa
1. **NUNCA mostrar stack traces** al usuario final — ni siquiera condicionalmente
2. **Custom error pages** para 403, 404, 419, 429, 500, 503
3. **Excepciones de dominio** con mensajes amigables, no técnicos
4. **Loggear el error completo** internamente, mostrar mensaje genérico al usuario
5. **JSON responses**: en API, devolver error genérico sin detalles internos
6. **`withExceptions`** en `bootstrap/app.php` actualmente está vacío — completar para producción

---

## Quick Security Checklist

Antes de commitear cualquier cambio, verificar:

- [ ] ¿Hay IDs auto-incrementales en URLs GET? → Usar slug/UUID
- [ ] ¿Las rutas nuevas tienen `auth` middleware? → Si requieren sesión
- [ ] ¿Hay endpoints POST/PUT/DELETE sin Policy? → Agregar `$this->authorize()`
- [ ] ¿Se usa `{!! !!}` en Blade? → Solo si el contenido es sanitizado (ej: markdown confiable)
- [ ] ¿Hay validación server-side? → Siempre, aunque el frontend ya valide
- [ ] ¿Hay rate limiting en rutas nuevas? → Login: sí. CRUD: considerar.
- [ ] ¿Se exponen datos sensibles en vistas/respuestas? → NO emails, tokens, IDs en tablas
- [ ] ¿APP_DEBUG=false en producción? → Sí
- [ ] ¿Las migraciones tienen índices y unique constraints? → Sí
- [ ] ¿Los Livewire components autorizan operaciones? → `$this->authorize()` o `@can`

## Commands

```bash
# Security audit de dependencias
composer audit

# Listar rutas (verificar que no expongan IDs)
php artisan route:list

# Verificar configuración de producción
php artisan config:show app
php artisan config:show session

# Tests de seguridad (todos los módulos)
php artisan test --filter=Policy
php artisan test --filter=Auth

# Full test suite
php artisan test

# Verificar middleware aplicado a rutas
php artisan route:list -v | Select-String -Pattern "auth|throttle|org\."

# Cachear config para producción
php artisan config:cache
```

## Resources

- **Auth module**: `app/Modules/Auth/` — login, register, password
- **Organization Middleware**: `app/Modules/Organization/Middleware/` — `EnsureOrganizationAccess`, `EnsureRole`
- **Policies**: `app/Modules/*/Policies/` — autorización por rol
- **Models**: `app/Modules/*/Models/` — soft deletes, UUIDs, slugs
- **Value Objects**: `app/Modules/Shared/ValueObjects/` — Email, Uuid, Slug
- **bootstrap/app.php**: Middleware aliases y exception handler
- **config/session.php**: `SECURE_COOKIE`, `HTTP_ONLY`, `SAME_SITE`, `ENCRYPT`
- **config/sanctum.php**: API token configuration
- **OWASP Top 10:2025**: https://owasp.org/Top10/
