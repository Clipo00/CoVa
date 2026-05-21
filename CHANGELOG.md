# Changelog

> Todos los cambios notables de este proyecto serán documentados en este archivo.
> Formato basado en [Keep a Changelog](https://keepachangelog.com/es-ES/1.0.0/).
>
> Para el contexto de negocio, decisiones y lecciones aprendidas detrás de estos cambios, ver [`docs/FEATURE_HISTORY.md`](docs/FEATURE_HISTORY.md).

---

## [Unreleased]

### Added
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

### Changed
- **OWASP A01 — Broken Access Control**: IDs auto-incrementales reemplazados por slugs en URLs GET de Organization show y BlueprintController
- `docs/PROJECT_SUMMARY.md` actualizado con estado real del código (125 tests, 237 assertions, seguridad)

### Fixed
- Excepción `TypeError` en `setTimeout` del toast por filtrar con `$event.detail.id` (undefined) — ahora captura `Date.now()` local
- `docs/PROJECT_SUMMARY.md` actualizado con estado real del código (117 tests, tabs dinámicas, transferencia, etc.)
- **Esquema de colores de badges por rol unificado** en todas las pantallas:
  - Owner → 🟣 purple, Maintainer → 🔵 blue, Developer → 🟢 green
  - Afecta: dashboard, org show, org list, org members (antes usaban colores inconsistentes)
- **Badge de categoría**: blueprint-list y favorites ahora muestran badge consistente (gray neutral)
- **Toast handler**: timeout ahora captura `id` local en vez de `$event.detail.id` inexistente

### Fixed
- **Toasts que nunca se borraban**: `setTimeout` filtraba por `$event.detail.id` (undefined) en vez del `id` local del toast — los toasts se acumulaban para siempre
- **Badge Owner en dashboard** sin `dark:bg-*-900/40`: en modo oscuro quedaba fondo lila claro con texto lila claro (1.15:1 de contraste)
- **Badge Developer en members** sin `dark:bg-*-900/40` y color gray incorrecto
- **Cards de tab-manager** con `bg-white` sin `dark:bg-gray-800`
- **Botones de presets/skills activos** sin variante dark (se veían en modo claro sobre fondo oscuro)
- **Hover de delete** en tab-manager: `text-red-600` sobre `bg-gray-800` daba solo 2.82:1
- **Contraste WCAG AA** en Org show, Org list, Blueprint-list: badges de rol usaban colores incorrectos según el rol

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
**Última actualización**: 2026-05-15
