# Changelog

> Todos los cambios notables de este proyecto serán documentados en este archivo.
> Formato basado en [Keep a Changelog](https://keepachangelog.com/es-ES/1.0.0/).
>
> Para el contexto de negocio, decisiones y lecciones aprendidas detrás de estos cambios, ver [`docs/FEATURE_HISTORY.md`](docs/FEATURE_HISTORY.md).

---

## [Unreleased]

### Changed
- **🦶 Footer de landing alineado con tabs del nav** — La sección "Producto" ahora refleja los tabs de navegación (Demo, Precios, Marketplace, Guía rápida). Marketplace ahora es un link clickeable. Agregado scroll suave al tope cuando se hace clic en links del footer estando scrolleado abajo.

### Added
- **📋 CLI List + Help + README (PR 4 of 6)** — Blueprint listing, help improvements, and documentation:
  - `ListCommand` — `cova vault:list [-g|--with-descriptions]` displays accessible blueprints as a formatted table with slug and title (and descriptions with `-g` flag)
  - Error handling: 401 "Authentication failed", 403 "Plan required", network errors with friendly messages
  - Clear command descriptions for all commands (visible via `cova help`)
  - `cli/README.md` — full installation guide, API key setup, usage examples, troubleshooting, and build instructions
  - **Tests**: 6 new tests for ListCommand (table display, `-g` descriptions, 401/403/network errors, empty state)
  - See `openspec/changes/covar-cli/` for full specification
- **⏬ CLI Fetch (PR 5 of 6)** — `cova vault:fetch <slug>` scaffolds a full project from a blueprint:

  - `FetchCommand` — `cova vault:fetch <slug>` resolves blueprint via `GET /api/blueprints/{slug}` and scaffolds `.agent.md`, `.vscode/extensions.json`, `.vscode/mcp.json`, and `.env` with blueprint variables
  - Secret double-auth flow: detects `is_secret=true` variables, prompts for password via hidden input (`$this->secret()`), calls `POST /api/fetch/{slug}/verify`, writes decrypted values on success, warns with empty values on failure
  - MCP server mapping: transforms `mcp_servers` array into `{ "mcpServers": { name: { command, args } } }` format for VSCode
  - Graceful 404 handling: "Blueprint not found: {slug}" instead of generic "Not found"
  - Registered in `cli/bootstrap/init.php`
  - **Tests**: 9 new tests (56 assertions) covering all-4-files scaffold, minimal blueprint, secrets with correct/wrong password, 404/401/403/network errors, and empty variables
  - See `openspec/changes/covar-cli/` for full specification

- **📦 PHAR Build & E2E Verification (PR 6 of 6)** — Final PR wrapping up the CLI tool with PHAR build, smoke test, and build documentation:
  - `build-phar.php` — standalone build script that replicates Laravel Zero's `app:build` command without needing the Application bootstrap (bypasses PHP 8.4 `method_exists` incompatibility in Laravel Zero v2.0)
  - `cli/BUILD.md` — complete build documentation covering prerequisites, quick build, smoke test, known issues (PHP 8.4 compat, eager command instantiation), CI integration, and release checklist
  - PHAR binary (~11.5 MB): `php -d phar.readonly=0 build-phar.php` → `cli/builds/cova`
  - Smoke test passes: `php builds/cova help` shows command list with valid config
  - Vendor patch documented: PHP 8.4 `method_exists(null, ...)` fix in `vendor/laravel-zero/laravel-zero/app/Console/Application.php`
  - See `openspec/changes/covar-cli/` for full specification

- **🖥️ CLI Foundation (PR 3 of 6)** — Standalone CLI tool in `cli/` for fetching blueprints via API:
  - Laravel Zero v2.0.14 scaffold: `cli/composer.json`, `cli/config/config.php`, `cli/box.json`, `cli/cova` entry point, `cli/bootstrap/init.php`
  - `ApiClient` — Guzzle HTTP wrapper with config from `~/.config/cova/config.json`, Bearer token auth, error mapping (401→auth, 403→plan required, 404→not found, 429→rate limit, 500→server error, network→friendly message), methods `get()`, `post()`, `validateConnectivity()`
  - `ConfigSetKeyCommand` — `cova config:set-key <key>` with `--base-url` option, validates via `GET /api/me` before saving, 0600 permissions on Unix, preserves existing `base_url`
  - **Tests**: 14 tests (ApiClient: auth headers, error mapping, connectivity validation, base_url config; ConfigSetKeyCommand: valid/invalid key, existing config preservation, base-url override, permissions, network errors)
  - See `openspec/changes/covar-cli/` for full specification

- **🔌 API Foundation (PR 1 of 6)** — Sanctum-authenticated JSON API for CoVa CLI tool:
  - `routes/api.php` with `auth:sanctum` + `throttle:60,1` middleware
  - `EnsureApiAccess` middleware: Free plan returns 403 RFC 7807, Pro/Enterprise passes through
  - `BlueprintApiController`: `index()` (paginated, org-scoped, plan-gated listing) and `show()` (full resolution via `ResolveBlueprint` with secret masking)
  - `BlueprintOutput::toApiArray()`: JSON serialization with secret variable masking (`is_secret` → `value: ""`)
  - `bootstrap/app.php`: API route registration, `api.access` middleware alias, RFC 7807 JSON error rendering for `api/*` routes
  - **Tests**: 15 new tests (116 assertions) across middleware unit, DTO unit, and controller feature tests
  - See `openspec/changes/covar-cli/` for full specification

- **🔐 Auth API (PR 2 of 6)** — Sanctum-authenticated user profile and password-gated secret decryption:
  - `AuthApiController`: `me()` returns authenticated user + accessible organizations; `verifyPassword()` checks `Hash::check()` and returns decrypted secret variables
  - `routes/api.php`: Added `GET /api/me` (no plan gate) and `POST /api/fetch/{slug}/verify` (throttled to 5/min)
  - `bootstrap/app.php`: Fixed exception handler — `ValidationException` no longer incorrectly returns 500 for `api/*` routes
  - **Tests**: 10 new tests (41 assertions) covering user profile, multiple orgs, 401 without auth, password verification success/failure, empty secrets, 404 not found, missing password validation, and rate limiting
  - See `openspec/changes/covar-cli/specs/auth/spec.md` for full specification

### Changed
- **Presets & Skills → Segments** — The toggle-based preset/skill system with HTML markers (`<!-- BEGIN:preset:... -->`) replaced by the new segment CRUD system in TabManager.
- **Template data format** — Templates in `BlueprintServiceProvider` updated from flat presets/skills arrays to the new `segments[]` format with registry content.
- **i18n: Castilian Spanish standard** — All new and modified translation strings use neutral Castilian Spanish (Spain) instead of Rioplatense voseo. New `covar-i18n` skill enforces this.
- **Re-publishing (sync)** — Published blueprints can now be synced to update marketplace copies with latest changes.
- **Publish creates copy instead of transfer** — Publishing now creates a marketplace copy with emptied secrets; original stays in creator's org marked public.
- **Marketplace listing filter** — Listing now filters by `cova-marketplace` organization, removing duplicates.
- **Selectores con más espacio para el chevron** — Aumentado el padding derecho del ícono dropdown de `px-3` (12px) a `pr-4` (16px) en todos los select nativos del formulario de creación y edición.
- **Presets y skills cargan contenido editable** — Al activar un preset (SOLID, PSR-12, etc.) o skill en la pestaña AI Context, su contenido markdown se carga automáticamente en el textarea de reglas custom envuelto en marcadores `<!-- BEGIN:preset:... -->`. El usuario edita libremente; al desactivar el toggle, el bloque se elimina del textarea automáticamente.
- **Textarea de reglas custom más alto** — Aumentado de 3 a 6 filas para facilitar la edición del contenido cargado por presets.
- **Publicar crea copia, no transfiere** — Al publicar un blueprint se crea una copia en `cova-marketplace` con secretos vaciados. El original se queda en la organización del creador marcado como público. El creador mantiene acceso completo.
- **Sincronización (re-publicar)** — Si un blueprint ya está publicado, el botón cambia a "Sincronizar cambios". Al pulsarlo se actualiza la copia del marketplace con los últimos cambios del original y se notifica a los suscriptores.
- **Marketplace solo muestra blueprints del sistema** — El listado ahora filtra exclusivamente por la organización `cova-marketplace` (no por `is_public=true` global), eliminando duplicados.
- **Votación abierta a todos los usuarios autenticados** — Cualquier usuario con sesión puede votar en blueprints del marketplace. Ya no se requiere ser miembro de la organización.
- **Sin auto-voto** — No se puede votar en blueprints propios (`created_by` check en policy).
- **Las suscripciones consumen slots del plan** — Suscribirse a un blueprint del marketplace ahora cuenta contra `max_blueprints_per_org` igual que crear uno nuevo.
- **Variables de entorno opcionales en creación** — El formulario de creación ya no precarga una variable vacía por defecto.
- **Selector de plantillas con opción vacía** — Se agregó `<option value="">Sin plantilla</option>` para detectar correctamente cambios de selección.
- **Mensajes de publish corregidos** — Los mensajes ahora reflejan correctamente que los secretos se vacían al publicar, no se exponen.

### Fixed
- **Blueprint collapsible toggle** — Variables section collapse/expand now works correctly with proper container constraints.
- **Download buttons not working** — Missing @stack('scripts') in app layout added.
- **Onboarding step indicator overflow** — Step labels shortened and responsive breakpoints added for mobile.
- **x-data quoting with @json** — Changed to single quotes in x-data attributes using @json.
- **Self-voting prevention** — Policy prevents users from voting on their own blueprints.
- **Subscription blueprint limit** — Marketplace subscriptions now count against max_blueprints_per_org.
- **Secret variables cleared on publish/subscribe** — Secret values properly emptied during publish and subscribe flows.
- **Slug uniqueness validation** — Pre-insert validation shows friendly error instead of SQL exception.
- **Publish redirect** — Publishing redirects to blueprint index (not show page in marketplace org).
- **Negation operator spacing** — Removed incorrect space after ! operator in LoginUser.
- **Cache files removed from repo** — .atl/ directory added to .gitignore and removed.
- **PHP code formatting** — Consistent pint formatting applied across all modules.
- **403 al publicar** — El blueprint ya no se transfiere al marketplace, por lo que el creador no pierde acceso.
- **Slug duplicado** — Validación pre-insert en `BlueprintCreateForm` que muestra error amigable en vez de excepción SQL.
- **Traducciones faltantes** — Agregadas `publish_section`, `publish_toggle`, `publish_help`, `template_label`, `template_empty`, `template_loading`, `live_preview`, `badge_public`, `slug_exists`, `publish_sync_button`, `publish_sync_confirm`.
- **Voseo en traducciones** — Corregido a castellano neutro (España) en todos los mensajes nuevos.
- **TabManager reactividad** — Cambiado prop `:tabs-config` a `:tabs` con key dinámica, permitiendo que las plantillas poblaran correctamente el editor.
- **Template data format** — Corregido nesting de `config` en datos de plantillas para compatibilidad con TabManager.

### Added
- **🔗 Friendly slug-based URLs `/b/{slug}`** — Blueprint show pages now use readable slugs instead of UUIDs. Route model binding with `{blueprint:slug}` and regex constraint `[a-z0-9]+(?:-[a-z0-9]+)*`. Legacy UUID requests receive 301 redirects to slug URLs. Mutation routes (create, edit, delete) retain UUIDs for security.
- **📥 Downloads section on blueprint show page** — Vault fetch CLI card with copyable `cova fetch` command. Download agent.md, per-segment .md files, and .env template as files using Alpine.js Blob downloads (no new routes). New `GenerateEnvTemplate` Action.
- **🔄 Auth loading spinners** — Login and register form submit buttons now show animated spinners during authentication with inputs disabled to prevent double submission.
- **🧩 AI Context Segment CRUD** — The AI Context tab refactored from flat preset/skill toggles to collapsible segment cards. New `AiContextSegment` DTO with types (preset, skill, custom). Segments are ordered, independently editable, and collapsible. Dropdown menus "Add preset" and "Add skill" load content from registry. Custom segments include free-text textarea.
- **📄 Agent.md router** — Generated `agent.md` now acts as a router including all segments in order with per-segment Markdown headings. `AgentGenerator::resolveSegments()` generates per-segment blocks.
- **📊 Segment-variable limit validation** — Segments now consume variable slots from the plan limit. `CreateBlueprint` and `UpdateBlueprint` validate combined segment + variable count against plan maximums.
- **📈 Dashboard polish** — 5 UI improvements: stats row (total orgs, blueprints, marketplace items), redesigned organization cards with role badges and counts, marketplace empty state, blueprint category badge on recent list, organization show blueprint count.
- **🧙 Onboarding wizard** — 4-step post-registration Livewire wizard: Welcome → Create Organization → Invite Team → Complete. Skip-all flow. Email verification banner (non-blocking, all steps). Browser refresh resilience via `onboarding_step` column. `EnsureOnboardingCompleted` middleware. Plan-limit exception handling. 20/20 tasks, 3 chained PRs.
- **📋 Template tabs populate TabManager** — Template selection during blueprint creation now correctly populates the TabManager with dynamic `wire:key` attributes.
- **🛒 Marketplace v1** — marketplace completo como módulo independiente:
  - **Listado público** (`/marketplace`): búsqueda, filtros por tags, ordenamiento (rating, suscriptores, reciente), paginación
  - **Vista de detalle** (`/marketplace/{uuid}`): contenido completo resuelto, variables enmascaradas para no-owners, stats
  - **Suscripción/Fork**: copia el blueprint a la organización del usuario con nuevo UUID. Relación trazable via `blueprint_subscriptions`. Sin límite de plan.
  - **Votación**: upvote/downvote con toggle, un voto por usuario, cached counters, anónimo al usuario (trazable internamente)
  - **Notificaciones in-app**: campanita con badge en nav, buzón en `/notifications`, notificaciones por update y delete de originales
  - **Delete flow**: al borrar un blueprint publicado → notifica suscriptores por lotes → desvincula copias (quedan limpias e independientes) → soft-delete
  - **Nuevo módulo**: `app/Modules/Marketplace/` con Actions, Controllers, Livewire, Models, Policies, Routes, Views, Tests
  - **4 tablas nuevas**: `blueprint_subscriptions`, `blueprint_votes`, `blueprint_tags`, `notifications`
  - **Tests**: 274 tests, 579 assertions (53 tests nuevos de marketplace)
  - **i18n**: `lang/{es,en}/marketplace.php` (~25 keys)
  - **Feature flag**: `MARKETPLACE_ENABLED` en `.env` controla visibilidad global del módulo
- **🔍 Blueprint Live Preview** — panel de vista previa en create/edit forms:
  - `ResolveBlueprintPreview` Action (in-memory, sin DB)
  - `BlueprintPreviewPanel` Livewire con debounce 300ms
  - Muestra agent.md, VSCode extensions, MCP servers en tiempo real
  - Secrets enmascarados para no-owners
  - Panel colapsable, actualización en template selection y carga inicial
- **🏷️ Tab Templates** — 3 plantillas preconfiguradas al crear blueprint:
  - Laravel Stack, Node.js Stack, Python Stack con IDs reales de extensiones VSCode y MCP
- **📢 Publish UI** — toggle `is_public` en edit form con `BlueprintPolicy::publish()`
  - Badge público/privado en show page y listados
  - Warning al borrar blueprint publicado
  - Landing marketplace preview con datos reales (top 6 públicos)
- **🧠 Presets & Skills expandidos** — 7 presets + 5 skills dinámicos:
  - Nuevos: Docker, CI/CD, LaravelConventions, TypeScriptStrict, API Design, ReactExpert, VueExpert
  - `tab-manager.blade.php` refactorizado a loops dinámicos (`AgentGenerator::presetNames()`)
- **👁️ Password visibility toggle** — ojito en login, register, y perfil (6 campos)
  - Alpine.js `x-data` con SVG eye/eye-off, i18n `show_password`/`hide_password`
- **🔑 API Token Management** — Gestión de tokens de API personales desde el perfil de usuario. Sanctum integrado (`HasApiTokens` trait, migración `personal_access_tokens`, prefijo `covar_`). Perfil reorganizado en 3 tabs Alpine.js (Datos, Cuenta, Seguridad). CRUD de tokens en tab Seguridad: crear (nombre + expiración máx 1 año + confirmación de contraseña), listar (nombre, último uso, expiración), revocar (confirmación de contraseña). Token en texto plano mostrado UNA sola vez con botón copiar. Plan-gating: solo Pro/Enterprise. RateLimiter 10/min. 24 tests nuevos.

### Changed
- **🏗️ Arquitectura de planes**: `plan_id` eliminado de `organizations`. El plan se lee del owner via accessor. `$organization->plan` → `$organization->owner->plan`.
- **🧪 Tests CSRF**: `ValidateCsrfToken` deshabilitado en `TestCase` base. 10 failures pre-existentes resueltos (de 419 a 200/302).
- **🧹 Limpieza i18n**: 25 claves muertas del mock data viejo de marketplace eliminadas de `landing.php`

### Security
- **Clear secrets on publish/subscribe** — Secret variable values are now properly emptied when publishing to marketplace and when subscribing/forking.
- **OWASP A07 — API token security**: Confirmación de contraseña requerida para crear Y revocar tokens. RateLimiter 10/min en componente. Token nunca almacenado en texto plano (Sanctum SHA-256). Prefijo `covar_` para detección de secrets en GitHub. One-time display con advertencia.
- **🔒 Security Validation Audit** — cierre de 6 gaps de seguridad y autorización (OWASP A01, A07):
  - **Track A (Fixes inmediatos)**:
    - Restricción de cambio de roles: solo el owner de la organización puede cambiar roles de miembros
    - Eliminación de blueprints: solo el owner puede eliminar (alineado con `BlueprintPolicy` SKILL.md)
    - Verificación de email en aceptación de invitaciones + límite de miembros por plan
    - Prevención de tabs duplicadas en blueprints (validación en `TabManager` + forms)
    - Chequeo de límite de blueprints en org destino al transferir
  - **Track B (Features nuevas)**:
    - Bloqueo de emails desechables/temporales en registro (regla `indisposable` vía `propaganistas/laravel-disposable-email`)
    - Verificación de email con signed URLs (`MustVerifyEmail`, 24h expiry)
    - MFA con código de 6 dígitos por email (10min expiry, single-use, rate-limited)
    - UI de MFA: formulario de challenge + toggle en perfil de usuario
    - Rate limiting en ruta MFA (`throttle:5,1`) + `RateLimiter` en Livewire (OWASP A07)
  - **Tests**: 171 tests, 320 assertions (46 tests nuevos total)
  - **i18n**: 39 nuevas claves sincronizadas (es/en) para verificación, MFA y throttle

### Added
- **💰 Sección de Pricing en Landing** — página de precios con 3 planes (Free/Pro/Enterprise):
  - Tarjetas comparativas con límites, features incluidos/excluidos, y CTAs
  - Plan Free: 2 orgs, 3 BP, 5 members, 50 variables — €0
  - Plan Pro: 5 orgs, 25 BP, 50 members, 150 variables — €9.99/mes
  - Plan Enterprise: ilimitado todo — contactar
  - Link "Precios" en nav superior y footer
  - Badge "Más popular" en plan Pro con sombra destacada
  - Traducciones ES/EN para todos los textos de pricing
  - Actualización de docs: `PROJECT_SUMMARY.md` y `FUNCTIONAL.md` reflejan 50/150 variables
  - Fix tests: límite de variables actualizado a 50 en Free
- **🎨 Colores por sección en variables** — cada grupo/fichero de variables ahora tiene un color asignado:
  - Nueva columna `section_color` en `blueprint_variables`
  - Paleta de 10 colores predefinidos asignados automáticamente
  - Color picker nativo en el formulario para que el usuario elija el color
  - Variables agrupadas por sección con borde lateral del color correspondiente
  - En la vista show: grupos con header coloreado, borde lateral, y badges para tipo/interactivo/secreto
- **🎨 Rediseño de formularios de Blueprint** — create/edit ahora tienen UI moderna y amigable:
  - Layout de dos columnas para título/slug y categoría/descripción
  - Cards con bordes redondeados (rounded-2xl), sombras suaves y bordes sutiles
  - Headers de sección con iconos coloridos (información, variables, tabs)
  - Inputs con estilo rounded-xl y focus rings suaves
  - Selects con flecha custom y apariencia mejorada
  - Botón de submit con sombra y efecto hover scale
  - Layout más amplio (max-w-4xl) para mejor aprovechamiento del espacio
  - Breadcrumbs rediseñados con iconos de flecha
  - Header con icono descriptivo y subtítulo
  - Nueva traducción `blueprint.create_description`
- **🖥️ Demo Section en Landing** — carousel de demostración con 3 pantallas rotatorias:
  - Dashboard con organizaciones y estadísticas
  - Formulario de crear organización con selector de plan
  - Formulario de crear blueprint con preview de variables
  - Navegación con dots y flechas, rotación automática cada 4 segundos
  - Mockups estilizados tipo browser con diseño consistente de CoVa
  - **Fix i18n**: Todos los textos de las 3 slides extraídos a traducciones (`demo_dash_*`, `demo_org_*`, `demo_bp_*`)
- **🎨 Logo SVG rediseñado** — icono simplificado de rueda de combinación:
  - Sin recuadro de caja fuerte, solo dial centrado sobre fondo azul (indigo-600)
  - Marcas de combinación cardinales + diagonales (8 total)
  - Indicador/puntero en la parte superior
  - Tamaño aumentado de w-8 a w-10 en nav, w-7 a w-8 en footer
  - Aplicado en `landing.blade.php` y `footer.blade.php`
- **🔖 Favicon SVG** — logo oficial en pestañas del navegador:
  - Favicon estándar (32×32) como data URI SVG en `<link rel="icon">`
  - Apple touch icon (180×180) para iOS/macOS
  - Aplicado en ambos layouts: `landing.blade.php` y `app.blade.php`
- **🌐 Fix i18n Terminal Animada** — textos de la terminal ahora responden al idioma:
  - Nuevas keys: `terminal_cmd_fetch`, `terminal_downloading`, `terminal_variables`, `terminal_files`, `terminal_ready`
  - Componente `animated-terminal` acepta prop `:lines` con contenido traducido
  - Eliminados textos hardcodeados en español del JavaScript de Alpine.js
- **🔍 Blueprint filters** — sistema de filtros por organización y categoría en el listado de blueprints:
  - Botón de filtro con icono de funnel y badge con cantidad de filtros activos
  - Dropdown con checkboxes para seleccionar organizaciones y categorías
  - Tags de filtros activos con botón individual para remover cada uno
  - Botón "Clear all" para limpiar todos los filtros de una vez
  - Checkbox "Save filters" que persiste los filtros activos en localStorage por usuario
  - El buscador de texto funciona sobre el conjunto de resultados ya filtrados
  - Mensaje diferenciado cuando no hay resultados vs. cuando no hay blueprints
  - Dropdown con transiciones, cierre al hacer click fuera y tecla Escape
  - Accesibilidad: `aria-label`, `aria-expanded`, `aria-controls`, `role="region"`, `aria-live="polite"` en tags
  - Seguridad: IDs de organizaciones validados contra las organizaciones del usuario
- **🌐 Internacionalización (i18n) completa** — sistema multi-idioma español/inglés:
  - 339 keys de traducción en ES (castellano de España) y EN (inglés)
  - Archivos lang organizados por módulo: `auth`, `blueprint`, `organization`, `dashboard`, `layouts`, `errors`, `shared`, `welcome`
  - `config/app.php` con `supported_locales` y locale por defecto `es`
  - Todas las vistas Blade (36 archivos) reemplazadas con `{{ __('module.key') }}`
  - Todos los mensajes PHP (Controllers, Actions, Livewire, Exceptions) reemplazados con `__()`
  - Strings con interpolación (`:name`, `:count`, `:max`, `:plan`) mediante placeholders
  - Manejo de HTML en traducciones con `{!! __() !!}`
  - Alpine.js store de confirmación con strings traducidas via Blade pre-render
- **🌐 Selector de idioma en UI** — LocaleSwitcher componente Alpine.js:
  - Middleware `SetLocaleFromCookie` en grupo `web` (después de `EncryptCookies`)
  - Ruta `GET /locale/{es|en}` que persiste elección en cookie + BD si está autenticado
  - Cookie `locale` excluida de cifrado de Laravel via `encryptCookies(except: ['locale'])`
  - Dropdown minimalista con indicador de idioma activo
  - Visible en auth layout (fixed top-right) y app layout (topbar junto a ThemeToggle)
- **💾 Persistencia de idioma en BD** — preferencia de usuario guardada en `users.locale`:
  - Migración `add_locale_to_users_table` — columna `locale` nullable
- **🏠 Landing Page** — nueva home de alto impacto que comunica ahorro de tiempo y seguridad:
  - Hero con terminal animada ejecutando `cova vault:fetch` (Alpine.js typing animation)
  - Sección "Pain Point": 3 cards sobre el caos de compartir .env por Slack
  - Sección "How it Works": 3 pasos (Define → Publish → Fetch) con conectores visuales
  - Marketplace Preview: grid con 6 plantillas populares (mock data)
  - CTA final con botón "Create free account" hacia registro
  - Layout dedicado `layouts/landing.blade.php` (sin nav de dashboard)
  - Scroll reveal con IntersectionObserver + Alpine.js directive
  - Respeto total de `prefers-reduced-motion`
  - SEO meta tags y Open Graph
  - 20 traducciones ES/EN en nuevo archivo `lang/{es,en}/landing.php`
  - Bundle JS: 0.31KB (0.22KB gzipped)
  - `SetLocaleFromCookie` prioriza: BD > cookie > config default
  - Al cambiar idioma autenticado → se guarda en BD
  - Al registrarse → hereda locale de la cookie
  - Al loguearse → si no tiene locale en BD, lo hereda de la cookie
- **OWASP Top 10:2025 — Security Sprint**:
  - 🛡️ `covar-security` skill con las 10 categorías OWASP (SIEMPRE cargada)
  - 🛡️ CSP Middleware (`EnsureSecurityHeaders`) con headers de seguridad globales
  - 🛡️ Páginas de error custom (403, 404, 419, 429, 500, 503) sin stack traces
  - 🛡️ Manejo de excepciones con logging completo + JSON API response
  - 🛡️ Rate limiting en rutas POST de Blueprint (30/min) y Organization (30/5/min)
  - 🛡️ `SESSION_ENCRYPT=true` y `SESSION_SECURE_COOKIE=true` por defecto
  - 🛡️ `SESSION_SECURE_COOKIE` agregado a `.env.example`
  - 🛡️ Security Roadmap documentado en `.agents/AGENTS.md`
- **Modo oscuro/nocturno** completo con ThemeToggle (`feat(ui): add dark mode with theme toggle and WCAG AA contrast`)
  - Componente `ThemeToggle` Alpine.js con animación sun/moon (700ms rotate+translate)
  - Anti-flash script en `<head>` para evitar flash blanco en carga
  - Persistencia en localStorage con detección de preferencia del sistema
  - Tailwind v4 `@custom-variant dark` en `app.css`
- **Modal de confirmación** Alpine.js (`feat(ui): replace native confirm() with Alpine.js confirmation dialog`)
  - Alpine.store('confirm') global con soporte dark/light mode
  - Backdrop con blur, animaciones x-transition, ícono de warning
  - Mensajes multilinea con `whitespace-pre-line`
  - Texto de botón configurable (Eliminar / Entendido)
- **Badge de categoría** en blueprints recientes de Organization show
- **Botón eliminar** en listado de blueprints con Policy y confirmación
- Documentación del proyecto (Fase 1-4)
- `docs/FUNCTIONAL.md` — Especificación funcional completa
- `docs/UI_SPECIFICATION.md` — Especificación de interfaz
- `docs/ARCHITECTURE.md` — Arquitectura y patrones
- `docs/CONTRIBUTING.md` — Guía de contribución
- `docs/TESTING.md` — Estrategia de testing
- `README.md` reemplazado por documentación real del proyecto
- **📦 Marketplace Features** — small-features-batch con 4 funcionalidades completas:
  - **Feature Flags** — `config/marketplace.php` con `MARKETPLACE_ENABLED` y `BILLING_ENABLED` (default false)
  - **Publicar Blueprint** — Action `PublishBlueprint` con verificación de plan (via `has_marketplace_publish`), owner gate, transferencia a org `cova-marketplace` y cambio a `is_public=true`
  - **Votación** — `BlueprintVote` model con composite unique `(user_id, blueprint_id)`, Action `VoteBlueprint` con upsert y recálculo de `aggregate_score`, rate limit 10/min
  - **Eliminar Miembro** — Action `RemoveOrganizationUser` con transacción, reasignación de blueprints al owner, rate limit 5/min
  - **Tests**: 207 tests, 374 assertions (36 nuevos tests para publish, vote, removeMember)
  - **i18n**: 18 nuevas claves sincronizadas (es/en) para publish, vote y remove member

### Changed
- **OWASP A01 — Broken Access Control**: IDs auto-incrementales reemplazados por slugs en URLs GET de Organization show y BlueprintController
- `docs/PROJECT_SUMMARY.md` actualizado con estado real del código (125 tests, 237 assertions, seguridad)

### Fixed
- **CSP bloqueaba assets de Vite en local**: el middleware `EnsureSecurityHeaders` usaba `http://[::1]:5173` (IPv6) en las directivas CSP, pero CSP no soporta IPv6 como source expression — el browser ignoraba la regla y bloqueaba scripts/styles. Se reemplazó por `http://127.0.0.1:5173` (IPv4 explícito) y se configuró Vite con `server.host: '127.0.0.1'`.
- Excepción `TypeError` en `setTimeout` del toast por filtrar con `$event.detail.id` (undefined) — ahora captura `Date.now()` local
- `docs/PROJECT_SUMMARY.md` actualizado con estado real del código (117 tests, tabs dinámicas, transferencia, etc.)
- **Esquema de colores de badges por rol unificado** en todas las pantallas:
  - Owner → 🟣 purple, Maintainer → 🔵 blue, Developer → 🟢 green
  - Afecta: dashboard, org show, org list, org members (antes usaban colores inconsistentes)
- **Badge de categoría**: blueprint-list y favorites ahora muestran badge consistente (gray neutral)
- **Toast handler**: timeout ahora captura `id` local en vez de `$event.detail.id` inexistente
- **Copilot Review fixes** (PR #8):
  - **XSS en dashboard**: `$plan->name` escapado con `e()` en `{!! __('dashboard.plan_limit_warning') !!}` — raw output sin escape permitía inyección HTML si el nombre del plan contenía código malicioso
  - **Nested `<strong>` en Organization show**: se pasaba `<strong>PlanName</strong>` como placeholder `:plan`, pero la traducción ya envolvía `:plan` en `<strong>` — resultado: `<strong><strong>PlanName</strong></strong>`. Se eliminó el `<strong>` del view, la traducción lo agrega
  - **Blank toast en CopyToClipboard**: cuando no se pasaba `successMessage`, el componente enviaba `dispatch('notify', message: '')` → toast vacío. Ahora defaulta a `__('shared.copied')`
  - **Misleading permission key en BlueprintCreateForm**: usaba `no_edit_permission` en un flujo de creación. Nueva key `no_create_permission` en EN y ES
  - **Duplicate key `transfer_select_org`** en `lang/en/blueprint.php` — la segunda entrada sobrescribía silenciosamente a la primera
  - **Open redirect en ruta locale**: `redirect()->back()` usaba header `Referer` directamente — permitía redirigir a URLs externas maliciosas. Se reemplazó por `url()->previous()` con validación same-origin
  - **Hardcoded locales en ruta y middleware**: `['es', 'en']` hardcodeado en `routes/web.php` y `SetLocaleFromCookie::SUPPORTED_LOCALES` — ahora leen de `config('app.supported_locales')`
  - **`free_plan_missing` en inglés en `lang/es/auth.php`**: traducido al español rioplatense

### Fixed
- **Toasts que nunca se borraban**: `setTimeout` filtraba por `$event.detail.id` (undefined) en vez del `id` local del toast — los toasts se acumulaban para siempre
- **Badge Owner en dashboard** sin `dark:bg-*-900/40`: en modo oscuro quedaba fondo lila claro con texto lila claro (1.15:1 de contraste)
- **Badge Developer en members** sin `dark:bg-*-900/40` y color gray incorrecto
- **Cards de tab-manager** con `bg-white` sin `dark:bg-gray-800`
- **Botones de presets/skills activos** sin variante dark (se veían en modo claro sobre fondo oscuro)
- **Hover de delete** en tab-manager: `text-red-600` sobre `bg-gray-800` daba solo 2.82:1
- **Contraste WCAG AA** en Org show, Org list, Blueprint-list: badges de rol usaban colores incorrectos según el rol
- **Modo oscuro no persistía en login**: `layouts/auth.blade.php` no incluía el script anti-flash ni el componente `ThemeToggle`, por lo que al navegar desde la landing (con dark mode activo) al login se perdía la preferencia y no había forma de cambiarla desde esa pantalla. Se agregó el script de detección de tema y `<livewire:shared.theme-toggle />` junto al locale switcher.
- **Mensaje diferenciado en botón de crear blueprint**: antes mostraba "Todas tus organizaciones han alcanzado el límite..." aunque el usuario no tuviera ninguna organización. Ahora diferencia entre: (1) no tiene organizaciones → mensaje "No tienes ninguna organización" con link para crear una; (2) tiene organizaciones pero sin cupo → mensaje original de límite alcanzado.
- **Migración de rioplatense a castellano en traducciones**: todos los archivos en `lang/es/` fueron actualizados para usar español de España (castellano) en lugar de rioplatense (voseo). Cambios: eliminá→elimina, tenés→tienes, podés→puedes, querés→quieres, probá→prueba, seleccioná→selecciona, agregá→agrega, actualizá→actualiza, ejecutá→ejecuta, iniciá→inicia, esperá→espera, volvé→vuelve, etc. Nueva skill `covar-i18n` creada para asegurar consistencia futura.
  - **Copilot Review fixes** (PR #9):
    - **i18n**: traducciones faltantes `blueprint.section_color` y `landing.go_to_dashboard` añadidas en ES/EN; fallbacks `??` con `__()` eliminados (nunca funcionan porque `__()` nunca devuelve `null`)
    - **Typo castellano**: "vuélve" corregido a "vuelve" en `lang/es/errors.php` (la RAE no tilda el imperativo de "volver")
    - **UI**: `ml-13` (clase Tailwind inexistente) corregido a `ml-12` en `create.blade.php`; variable `$borderColor` sin usar eliminada de `show.blade.php`; botón para usuarios autenticados en landing nav ahora dice "Ir al panel" en vez de "Iniciar sesión"
    - **Rutas**: `/` ahora redirige a `dashboard` si el usuario está autenticado, en lugar de mostrar siempre la landing
    - **Seguridad**: `assignSectionColors()` ya no sobrescribe el color elegido por el usuario con el color picker; validación de formato HEX (`#RRGGBB`) en `section_color` para prevenir inyección de estilos inline (XSS) si el payload es manipulado
    - **Meta tags**: descripciones Open Graph y Twitter en `landing.blade.php` ahora usan `strip_tags()` para evitar que HTML (`<strong>`) filtre a previews de links; eliminado `og:image` apuntando a archivo inexistente
    - **Scroll reveal**: directive `x-reveal` de Alpine.js ahora aplica `revealed` también a descendientes `.reveal` (CSS `.revealed .reveal`), no solo al mismo elemento — los títulos y cards animan correctamente al entrar en viewport
    - **Plan names i18n**: nombres de planes (`Free`, `Pro`, `Enterprise`) en `pricing.blade.php` movidos a traducciones (`landing.plan_name_*`) para cumplir con skill `covar-i18n`
    - **Default value**: `CreateBlueprint` y `UpdateBlueprint` ahora usan comparación `!== ''` en lugar de `!empty()` para preservar el string `'0'` como valor válido de `default_value` (antes se guardaba como `null`)
    - **Docs**: `docs/FEATURE_HISTORY.md` actualizado con árbol completo de partials de landing (demo, pricing, footer) y eliminado conteo obsoleto de "25 keys"
  - **Fix en tiempo real de colores por sección**: hook `updatedVariables()` en `ManagesVariables` asigna `section_color` automáticamente cuando el usuario escribe una sección, evitando el bug donde el color picker solo aparecía al añadir la siguiente variable (desfase de un paso entre el mapa de la vista y la propiedad `section_color` de Livewire)

---

## [0.4.0] — 2026-05-05

### Added
- **Tabs dinámicas con arquitectura de plugins** (`feat(blueprint): add dynamic tabs system with plugin architecture`)
  - Soporte para 3 tipos de tab: VSCode Extensions, MCP Servers, AI Context
  - `TabManager` Livewire: add/remove/reorder tabs
  - `TabType` enum extensible
  - Configuración por tipo guardada en JSON (`tabs_config`)
- **AI Context tab** con generación de `agent.md`
  - Presets predefinidos seleccionables
  - Skills configurables
  - Custom rules en textarea
  - Preview de `agent.md` en blueprint show con copy-to-clipboard
- **Configuración de Agentes AI y Skills** (`feat(agents): add AI agent configuration and CoVa-specific skills`)
  - Estructura de presets y skills en `app/Modules/Blueprint/Tabs/AiContext/`
  - Tests unitarios para `AgentGenerator`
- **Secciones colapsables** en blueprint show (`feat(blueprint): collapsible sections and copy install command`)
  - Acordeón para Variables, VSCode, MCP, AI Context
  - Botón "Copiar comando de instalación"
- **Listado cross-organización** (`fix(blueprint): list all blueprints across user organizations instead of hardcoding org id`)
  - Blueprints index muestra blueprints de todas las orgs del usuario

### Fixed
- AI Context tab no se mostraba por error de tipo (`instanceof string` siempre false)
- Error `implode()` cuando args de MCP server era string en lugar de array
- Tabs no se guardaban ni renderizaban correctamente en create/edit
- CRUD create/edit fallaba con múltiples variables (validación de arrays)
- Sincronización de variables en edit form desde `VariableManager`

---

## [0.3.0] — 2026-05-04

### Added
- **Transferencia de blueprints entre organizaciones** (`feat(blueprint): transfer blueprint between organizations`)
  - Modal de selección de org destino
  - Validación de ownership y límites de plan
- **Soft delete y restauración** para blueprints y organizaciones
  - `DeleteBlueprint` / `RestoreBlueprint` Actions
  - `DeleteOrganization` / `RestoreOrganization` / `ForceDeleteOrganization` Actions
  - Página `/blueprints/deleted` (papelera)
  - Blueprints eliminados ocultos de listados normales
- **Gestión de miembros** completa (`feat(organization): member management and role updates`)
  - Añadir miembro directo por email (Owner)
  - Cambiar rol de miembro (Owner)
  - Eliminar miembro de org
- **Invitaciones por token** (`feat(organization): implement member management and invitations`)
  - Generación de token UUID con expiración (48h)
  - Flujo: email → link → aceptar → unirse a org
  - Soporte para usuarios no registrados (registro + auto-aceptar)
- **Secciones/grouping en variables** (`feat(blueprint): add section/grouping to variables`)
  - Campo `section` en `blueprint_variables`
  - Variables agrupadas visualmente en tabla
- **Selector de categoría** en create/edit de blueprint (`feat(blueprint): add category select to create form`)
- **Página de edición de organización** (`feat(organization): implement organization edit page`)
- **Marketplace preparation** (`feat(preparation): create marketplace organization for Phase 2`)
  - Org de marketplace creada en seeder
  - Flag `has_marketplace_publish` en planes
  - Campo `is_public` en blueprints

### Changed
- Mejorada sincronización de variables entre `VariableManager` y formularios padre
- Variables en edit form se cargan correctamente desde BD

---

## [0.2.0] — 2026-05-03

### Added
- **Dashboard** con vista de organizaciones y stats (`feat(dashboard): organization overview with plan limits`)
  - Grid de tarjetas de orgs
  - Blueprints recientes
  - Warning de límites alcanzados
- **Página de favoritos** real (`feat(blueprint): implement favorites page with real listing`)
  - `/blueprints/favorites` con listado filtrado
- **Página de edición de blueprint** (`feat(blueprint): implement blueprint edit page`)
  - Formulario completo con variables y tabs
  - Sincronización de `VariableManager` y `TabManager`
- **Variable Manager** (`feat(blueprint): variable manager for environment variables`)
  - CRUD inline de variables .env
  - Tipos: Fixed, Empty
  - Flags: interactive, secret
  - Drag & drop para reordenar
- **Validación de límites por plan** (`feat(blueprint): validate variable limits per plan`)
  - Límite de variables por blueprint
  - Límite de blueprints por org
  - Límite de orgs por usuario
  - Límite de miembros por org
- **Políticas de autorización** (`feat(roles): policies, middleware, authorization`)
  - `BlueprintPolicy`: view, create, update, delete, transfer
  - `OrganizationPolicy`: view, update, delete, invite, manage members
  - Middleware: `EnsureOrganizationAccess`, `EnsureRole`
- **UI responsive y feedback** (`feat(ui): responsive layout, toasts, copy-to-clipboard`)
  - Layout adaptable mobile/tablet/desktop
  - Sistema de toasts (éxito, error, warning)
  - Componente `CopyToClipboard` reutilizable
- **Navegación** (`fix(ui): add navigation and organization show page`)
  - Sidebar con links a Dashboard, Orgs, Blueprints, Favorites
  - Página de detalle de organización

### Fixed
- Plan Free se asigna correctamente al registrar (`fix(auth): assign free plan on user registration`)
- Password hashing y validación de registro (`fix(auth): password hashing and register form validation`)

---

## [0.1.0] — 2026-05-03

### Added
- **Autenticación** completa (`feat(auth): login, register, logout with tests`)
  - Login form (Livewire) con validación
  - Register form (Livewire) con asignación automática de plan Free
  - Logout con invalidación de sesión
  - 9 tests de auth
- **Módulo Shared** — Infraestructura transversal (`feat(shared): plans, categories, value objects, services`)
  - Modelo `Plan` con límites configurables
  - Modelo `Category` (8 categorías predefinidas)
  - Value Objects: `Email`, `Uuid`, `Slug`
  - Services: `PasswordHasher`, `UuidGenerator`, `JsonValidator`
  - 34 tests de shared
- **Organizaciones** — CRUD básico (`feat(organization): crud, plan limits, invitations`)
  - Crear org con slug auto-generado
  - Herencia de plan desde usuario
  - Soft deletes
  - 11 tests de organization
- **Blueprints** — CRUD inicial (`feat(blueprint): crud, variables, favorites, plan limits`)
  - Crear blueprint con UUID v4 único
  - Asociación a organización y categoría
  - Favoritos (toggle)
  - Variables .env (estructura inicial)
  - 7 tests de blueprint
- **Arquitectura modular** (`feat(structure): generate base structure`)
  - Sistema de módulos autocontenidos
  - `ModuleServiceProvider` con auto-registro
  - `RouteServiceProvider` carga rutas por módulo
  - Convenciones de carpetas y namespaces
- **Documentación inicial** (`docs: add project summary with architecture and decisions`)
  - `docs/PROJECT_SUMMARY.md` con arquitectura, decisiones técnicas, y roadmap

---

## [0.0.1] — 2026-05-02

### Added
- Initial commit
- Estructura base de Laravel 13

---

## Notas de Versionado

Este proyecto no sigue estrictamente [Semantic Versioning](https://semver.org/lang/es/) durante la fase MVP. Las versiones reflejan milestones de desarrollo:

- **0.0.x**: Estructura base
- **0.1.x**: MVP Foundation (auth, orgs, blueprints básicos)
- **0.2.x**: MVP Core (dashboard, roles, variables, UI)
- **0.3.x**: Colaboración (invitaciones, soft delete, transfer)
- **0.4.x**: Tabs & AI (sistema dinámico, agent.md, marketplace prep)
- **0.5.x** (próximo): API REST + CLI (Fase 3 del roadmap)

---

**Formato**: [Keep a Changelog](https://keepachangelog.com/)  
**Changelog generado desde**: Conventional commits del repo (git log)  
**Última actualización**: 2026-05-28
