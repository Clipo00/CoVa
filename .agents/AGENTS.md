# CoVa - AI Agent Configuration

> Configuration Vault: Zero-latency environment setup for modern developers.

## Project Context

**CoVa** es una plataforma SaaS desarrollada en Laravel 13 con arquitectura modular. Gestiona la creación y compartición de **Blueprints** (plantillas de configuración) que automatizan el setup de proyectos.

### Tech Stack

| Capa | Tecnología |
|------|------------|
| **Framework** | Laravel 13 (PHP 8.3+) |
| **Frontend** | Blade + Livewire 3 + Tailwind CSS |
| **Auth** | Custom (Breeze-like) + Sanctum (API-ready) |
| **BD** | SQLite (dev) / MySQL (prod) |
| **Tests** | PHPUnit 12.5 |
| **Build** | Vite |

### Arquitectura Modular

```
app/Modules/
├── Auth/              # Autenticación y usuarios
├── Organization/      # Organizaciones, roles, invitaciones
├── Blueprint/         # Blueprints, variables, favoritos
└── Shared/            # Código transversal (planes, categorías, VO)
```

Cada módulo sigue el patrón:
- **Actions**: Casos de uso / Comandos (ej: `CreateBlueprint`, `LoginUser`)
- **Controllers**: Orquestación HTTP
- **DTOs**: Objetos de transferencia de datos
- **Livewire**: Componentes reactivos (Forms, Tables, Components)
- **Models**: Entidades del dominio
- **Policies**: Autorización basada en roles
- **Routes**: Definición de rutas
- **Views**: Templates Blade
- **Tests**: Unitarios y Feature

### Conventions

- **Namespacing**: `App\Modules\{ModuleName}`
- **Actions**: `execute()` método principal, inyección de dependencias
- **Livewire Forms**: Validación en `rules()`, `validateOnly()` en `updated()`
- **Value Objects**: Inmutables, validación en constructor, `__toString()`
- **DTOs**: Data classes con propiedades readonly
- **Soft Deletes**: En Blueprint y Organization
- **UUIDs**: Todos los blueprints usan UUID v4
- **Slugs**: Lowercase, números y guiones uniquement

### Commands

```bash
# Instalar dependencias
composer install

# Configurar entorno
cp .env.example .env
php artisan key:generate

# Base de datos y seeders
php artisan migrate:fresh --seed

# Tests
php artisan test

# Servidor local
php artisan serve

# Setup completo (dev)
composer setup
```

---

## Skill Router

Los skills se cargan automáticamente según el contexto detectado. Esta es la matriz de decisión:

### Context → Skill Mapping

| Context | Skill to Load |
|---------|---------------|
| **Writing Go tests** | `go-testing` |
| **Creating new AI skills** | `skill-creator` |
| **Running judgment-day review** | `judgment-day` |
| **Laravel Actions** | `covar-laravel-action` |
| **Laravel Livewire Components** | `covar-laravel-livewire` |
| **Laravel Policies / Authorization** | `covar-laravel-policy` |
| **Laravel Models & Migrations** | `covar-laravel-model` |
| **Laravel Routes & Controllers** | `covar-laravel-controller` |
| **Laravel Tests** | `covar-laravel-test` |
| **Laravel Value Objects & DTOs** | `covar-laravel-dto` |
| **Laravel Security / OWASP** | `covar-security` |
| **SDD workflow (spec-driven)** | `sdd-*` (auto-detectado por prefijo) |

### Skill Auto-Detection Rules

1. **Por nombre de archivo/directorio**:
   - `Actions/*` → cargar `covar-laravel-action`
   - `Livewire/*` → cargar `covar-laravel-livewire`
   - `Policies/*` → cargar `covar-laravel-policy`
   - `Models/*` → cargar `covar-laravel-model`
   - `Controllers/*` → cargar `covar-laravel-controller`
   - `Tests/*` → cargar `covar-laravel-test`
   - `lang/*` → cargar `covar-i18n`
   - `*.blade.php` → cargar `covar-i18n` (validación de traducciones en vistas)

2. **Por extensión/comportamiento**:
   - extiende `Livewire\Component` → `covar-laravel-livewire`
   - implements authorization logic → `covar-laravel-policy`
   - tiene método `execute()` → `covar-laravel-action`

3. **Por operaciones específicas**:
   - `create_blueprint` / `CreateBlueprint` → `covar-laravel-action` (Blueprint context)
   - `authenticate` / `LoginUser` → `covar-laravel-action` (Auth context)
   - soft deletes → `covar-laravel-model`

4. **Por consideración de seguridad (SIEMPRE)**:
   - `covar-security` se carga AUTOMÁTICAMENTE en toda sesión de edición de código
   - Se combina con cualquier otra skill que aplique (seguridad es transversal)
   - No requiere un trigger específico — es parte del contexto base del proyecto

5. **Por archivos/patrones de seguridad**:
   - `bootstrap/app.php` (exceptions handler, middleware) → también `covar-security`
   - `config/session.php`, `config/sanctum.php`, `config/cors.php` → también `covar-security`
   - `Middleware/*` → también `covar-security` (configuraciones de seguridad)
   - rutas con `throttle` o middleware restrictivo → también `covar-security`
   - operaciones de eliminación/restauración → también `covar-security`
   - manejo de errores, páginas 403/404/500/419 → también `covar-security`

6. **Por idioma**:
   - Detectar el idioma del mensaje del usuario (español o inglés) y adaptar las preguntas y prompts en el mismo idioma.
   - Cuando el usuario "pida features" —palabras clave como `feature`, `feature request`, `funcionalidad`, `pedir features`, `pedir funcionalidades`— el agente debe:
     - Identificar si el texto está en español o en inglés usando simples heurísticos de palabras clave.
     - Formular las preguntas de aclaración y los mensajes de seguimiento en el idioma detectado.
   - Ejemplos:
     - "¿Puedes añadir esta feature?" → Detecta español, responde en español.
     - "I want a new feature for blueprints" → Detecta inglés, responde en English.

7. **Por internacionalización (i18n)**:
   - `lang/*` → cargar `covar-i18n`
   - `*.blade.php` con texto visible → cargar `covar-i18n` (validar que use `__()`)
   - Archivos PHP con mensajes para usuarios (exceptions, validations, toasts) → `covar-i18n`
   - Edición de traducciones existentes → `covar-i18n`
   - Se combina con cualquier skill de dominio que genere texto nuevo

---

## Project Skills

| Skill | Description | Location |
|-------|-------------|----------|
| `covar-laravel-action` | Patrones para Actions en CoVa | [.agents/skills/covar-laravel-action/SKILL.md](.agents/skills/covar-laravel-action/SKILL.md) |
| `covar-laravel-livewire` | Patrones para Livewire en CoVa | [.agents/skills/covar-laravel-livewire/SKILL.md](.agents/skills/covar-laravel-livewire/SKILL.md) |
| `covar-laravel-policy` | Patrones para Policies en CoVa | [.agents/skills/covar-laravel-policy/SKILL.md](.agents/skills/covar-laravel-policy/SKILL.md) |
| `covar-laravel-model` | Patrones para Models en CoVa | [.agents/skills/covar-laravel-model/SKILL.md](.agents/skills/covar-laravel-model/SKILL.md) |
| `covar-laravel-controller` | Patrones para Controllers en CoVa | [.agents/skills/covar-laravel-controller/SKILL.md](.agents/skills/covar-laravel-controller/SKILL.md) |
| `covar-laravel-test` | Patrones para Tests en CoVa | [.agents/skills/covar-laravel-test/SKILL.md](.agents/skills/covar-laravel-test/SKILL.md) |
| `covar-laravel-dto` | Patrones para DTOs y Value Objects | [.agents/skills/covar-laravel-dto/SKILL.md](.agents/skills/covar-laravel-dto/SKILL.md) |
| `covar-i18n` | Internacionalización: todo texto en castellano + inglés, sincronizado | [.agents/skills/covar-i18n/SKILL.md](.agents/skills/covar-i18n/SKILL.md) |
| `covar-security` | OWASP Top 10:2025 — Seguridad integral en CoVa (SIEMPRE activa) | [.agents/skills/covar-security/SKILL.md](.agents/skills/covar-security/SKILL.md) |
| `skill-creator` | Crear nuevas skills para CoVa | [~/.config/opencode/skills/skill-creator/SKILL.md](../../.config/opencode/skills/skill-creator/SKILL.md) |
| `sdd-init` | Inicializar SDD en el proyecto | [~/.config/opencode/skills/sdd-init/SKILL.md](../../.config/opencode/skills/sdd-init/SKILL.md) |
| `sdd-propose` | Crear propuesta de cambio | [~/.config/opencode/skills/sdd-propose/SKILL.md](../../.config/opencode/skills/sdd-propose/SKILL.md) |
| `sdd-spec` | Escribir especificaciones | [~/.config/opencode/skills/sdd-spec/SKILL.md](../../.config/opencode/skills/sdd-spec/SKILL.md) |
| `sdd-design` | Crear diseño técnico | [~/.config/opencode/skills/sdd-design/SKILL.md](../../.config/opencode/skills/sdd-design/SKILL.md) |
| `sdd-tasks` | Descomponer en tareas | [~/.config/opencode/skills/sdd-tasks/SKILL.md](../../.config/opencode/skills/sdd-tasks/SKILL.md) |
| `sdd-apply` | Implementar tareas | [~/.config/opencode/skills/sdd-apply/SKILL.md](../../.config/opencode/skills/sdd-apply/SKILL.md) |
| `sdd-verify` | Verificar implementación | [~/.config/opencode/skills/sdd-verify/SKILL.md](../../.config/opencode/skills/sdd-verify/SKILL.md) |
| `sdd-archive` | Archivar cambio completado | [~/.config/opencode/skills/sdd-archive/SKILL.md](../../.config/opencode/skills/sdd-archive/SKILL.md) |

---

## Global Rules

### NEVER

- Agregar "Co-Authored-By" o atribución de IA a commits. Usar conventional commits únicamente.
- Hacer build después de cambios.
- Asumir respuestas sin verificar. Decir "dejame verificar" y revisar código/docs primero.
- Aceptar afirmaciones del usuario sin verificación.
- Crear archivos de documentación proactivamente (*.md o README).

### ALWAYS

- Usar `declare(strict_types=1);` en todos los archivos PHP.
- Aplicar el patrón del módulo correspondiente (Actions, Livewire, etc.).
- Seguir las convenciones de CoVa documentadas arriba.
- Para decisiones técnicas: (1) explicar el problema, (2) proponer solución con ejemplos, (3) mencionar tools/resources.

### Cuando el usuario pregunta algo técnico

1. Primero verificar en el código base (grep, glob, read)
2. Si no se encuentra, admitir que no se sabe y proponer investigar
3. Nunca fingir conocer la respuesta

---

## CoVa Domain Knowledge

### Plans & Limits

| Plan | Orgs | Blueprints/Org | Miembros/Org | Variables/BP | API | Marketplace |
|------|------|----------------|--------------|--------------|-----|-------------|
| **Free** | 2 | 3 | 5 | 50 | ❌ | ❌ |
| **Pro** | 5 | 25 | 50 | 150 | ✅ | ✅ |
| **Enterprise** | ∞ | ∞ | ∞ | ∞ | ✅ | ✅ |

Los límites se validan en Actions, lanzando excepciones custom:
- `MaxBlueprintsReachedException`
- `MaxVariablesReachedException`
- `MaxOrganizationsReachedException`

### Roles por Organización

| Rol | Permisos |
|-----|----------|
| **Owner** | Todo: CRUD org, miembros, blueprints, invitaciones |
| **Maintainer** | CRUD blueprints, gestionar miembros (no eliminar org) |
| **Developer** | CRUD blueprints, ver org y miembros |

### Blueprint Variables

| Tipo | Comportamiento CLI |
|------|-------------------|
| **Fixed** | Valor predefinido, se inyecta tal cual |
| **Empty** | Se crea la variable sin valor |

Flags: `is_interactive`, `is_secret`

### Value Objects del módulo Shared

- `Email`: Validación + lowercase automático
- `Uuid`: UUID v4 + generación automática
- `Slug`: Solo lowercase/números/guiones, sanitización

### Services del módulo Shared

- `PasswordHasher`: Wrapper sobre `password_hash/verify`
- `UuidGenerator`: Genera instancias de Uuid VO
- `JsonValidator`: Valida, decodifica, codifica JSON

---

## Security Roadmap (Next Steps)

| Prioridad | OWASP | Tarea | Estado |
|-----------|-------|-------|--------|
| 🔴 Alta | A02 | **Deploy config**: cachear config (`php artisan config:cache`), generar `APP_KEY`, verificar `APP_DEBUG=false` en producción | Pendiente |
| ✅ Hecho | A02 | **CSP fine-tuning (dev)**: Vite IPv6 no soportado por CSP — se forzó IPv4 en `server.host` y se actualizaron origenes CSP a `127.0.0.1:5173` | Hecho en `fix/csp-vite-ipv6` |
| 🟡 Media | A09 | **Audit logging**: implementar logging estructurado de operaciones sensibles (login, delete, invite, role changes) con canal separado `audit` | Pendiente |
| 🟡 Media | A08 | **Implementar signed URLs** para invitaciones y password reset si no existen | Pendiente |
| 🟢 Baja | A06 | **Revisar rate limits**: ajustar thresholds según uso real en producción | Pendiente |
| 🟢 Baja | A07 | **MFA**: evaluar implementación de autenticación de dos factores para organizations Enterprise | Pendiente |
| 🟢 Baja | A03 | **Dependency audit automático**: agregar `composer audit` y `npm audit` al pipeline CI/CD | Pendiente |

### Ya implementado (v1.0)

| OWASP | Medida | Archivos |
|-------|--------|----------|
| A01 | Slugs en URLs, no IDs auto-incrementales | Organization show, BlueprintController |
| A01 | Policies por modelo (BlueprintPolicy, OrganizationPolicy) | `app/Modules/*/Policies/` |
| A01 | Open redirect protection en locale route | `routes/web.php` — `url()->previous()` con validación same-origin |
| A02 | CSP + HSTS + Referrer-Policy + security headers | `EnsureSecurityHeaders` middleware global |
| A02 | Locales desde config, no hardcodeados | `routes/web.php`, `SetLocaleFromCookie` — `config('app.supported_locales')` |
| A04 | `SESSION_ENCRYPT=true`, `SESSION_SECURE_COOKIE=true` | `config/session.php`, `.env.example` |
| A05 | Blade escaping (`{{ }}`), Alpine `x-text`, Eloquent ORM | Transversal |
| A05 | XSS prevention: `e()` en raw output con interpolación | `dashboard.blade.php` — `{!! __() !!}` con parámetros escapados |
| A06 | Rate limiting en POST routes CRUD | Blueprint (30/min), Organization (30/5 min) |
| A07 | Session regeneration on login, CSRF, httpOnly cookies | Laravel built-in |
| A10 | Custom error pages + exception logging + JSON API handler | `resources/views/errors/*`, `bootstrap/app.php` |

---

## Resources

- **Project Summary**: [docs/PROJECT_SUMMARY.md](docs/PROJECT_SUMMARY.md)
- **Laravel Docs**: https://laravel.com/docs/13.x
- **Livewire Docs**: https://livewire.laravolt.dev/docs/
- **Tailwind CSS**: https://tailwindcss.com/
