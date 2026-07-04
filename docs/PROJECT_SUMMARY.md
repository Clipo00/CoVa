# CoVaR - The Config Vault

> Zero-latency environment setup for modern developers.

## Visión General

CoVaR es una plataforma SaaS desarrollada en Laravel 13 que centraliza la lógica de configuración de entornos de desarrollo. Permite a equipos crear, compartir y ejecutar **Blueprints** (plantillas de configuración) que automatizan el setup de proyectos desde `git clone` hasta productivo en segundos.

## Arquitectura

> Para profundizar en patrones, flujo de request, y guía de módulos, ver [`ARCHITECTURE.md`](ARCHITECTURE.md).

### Monolito Modular

El proyecto sigue una arquitectura de **monolito modular** donde cada dominio de negocio está autocontenido:

```
app/Modules/
├── Auth/              # Autenticación y usuarios
├── Organization/      # Organizaciones, roles, invitaciones
├── Blueprint/         # Blueprints, variables, favoritos
├── Marketplace/       # Marketplace público, suscripciones, votación, notificaciones
└── Shared/            # Código transversal (planes, tags, VO)
```

Cada módulo contiene:
- **Actions**: Casos de uso / Comandos
- **Controllers**: Orquestación HTTP
- **DTOs**: Objetos de transferencia de datos
- **Livewire**: Componentes reactivos
- **Models**: Entidades del dominio
- **Policies**: Autorización
- **Routes**: Definición de rutas
- **Views**: Templates Blade
- **Tests**: Unitarios y Feature

## Stack Tecnológico

| Capa | Tecnología |
|------|------------|
| **Framework** | Laravel 13 (PHP 8.3+) |
| **Frontend** | Blade + Livewire 3 + Tailwind CSS |
| **Auth** | Laravel Breeze-like (custom) + Sanctum (listo para API) |
| **BD** | SQLite (dev) / MySQL (prod) |
| **Tests** | PHPUnit 12.5 |
| **Build** | Vite |

## Módulos Implementados

### Módulo Auth

**Responsabilidad**: Gestión de identidad, registro, login, logout.

**Patrón**: Actions + DTOs + Livewire Forms

| Componente | Descripción |
|------------|-------------|
| `RegisterUser` Action | Crea usuario con plan Free por defecto |
| `LoginUser` Action | Autentica con credenciales |
| `LogoutUser` Action | Invalida sesión y tokens |
| `CreateApiToken` Action | Crea token Sanctum con plan-gating |
| `RevokeApiToken` Action | Revoca token Sanctum del usuario |
| `LoginForm` Livewire | Formulario reactivo con validación en tiempo real |
| `RegisterForm` Livewire | Registro con validación y redirect al dashboard |
| `ApiTokenManager` Livewire | Gestión de tokens API en perfil de usuario |

**Rutas**:
- `GET /login` — Formulario de login
- `GET /register` — Formulario de registro
- `POST /logout` — Cierre de sesión

---

### Módulo Organization

**Responsabilidad**: Tenancy, colaboración, roles, invitaciones.

#### Roles por Organización

| Rol | Permisos |
|-----|----------|
| **Owner** | Todo: CRUD org, miembros, blueprints, invitaciones |
| **Maintainer** | CRUD blueprints, gestionar miembros (no eliminar org) |
| **Developer** | CRUD blueprints, ver org y miembros |

#### Modelo de Datos

```
organizations (id, slug, name, owner_id, plan_id, softDeletes)
organization_user (id, organization_id, user_id, role, timestamps)
organization_invitations (id, organization_id, email, token, role, expires_at, used_at)
```

#### Actions

| Action | Descripción |
|--------|-------------|
| `CreateOrganization` | Valida límite de plan, asigna plan heredado |
| `UpdateOrganization` | Actualiza nombre/slug |
| `DeleteOrganization` | Soft delete |
| `RestoreOrganization` | Recupera del soft delete |
| `ForceDeleteOrganization` | Eliminación permanente |
| `InviteUser` | Genera token de invitación con expiración |
| `AcceptInvitation` | Valida token, añade usuario a org |
| `CreateOrganizationUser` | Crea usuario directo por Owner |
| `UpdateOrganizationUserRole` | Actualiza rol de un miembro |

#### Livewire Components

| Componente | Descripción |
|------------|-------------|
| `CreateOrganizationForm` | Formulario de creación con validación de límite de plan |
| `OrganizationList` | Tabla de organizaciones del usuario |

#### Middleware

| Middleware | Uso |
|------------|-----|
| `EnsureOrganizationAccess` | Verifica membresía |
| `EnsureRole` | Verifica rol específico |

**Rutas**:
- `GET /organizations` — Listado
- `GET /organizations/create` — Crear org
- `GET /organizations/{slug}` — Detalle de org
- `GET /organizations/{slug}/edit` — Editar org
- `POST /organizations/{slug}/update` — Actualizar org
- `GET /organizations/{slug}/members` — Gestión de miembros
- `POST /organizations/{slug}/members/store` — Añadir miembro directo
- `POST /organizations/{slug}/members/{user_id}/role` — Cambiar rol
- `POST /organizations/{slug}/invite` — Enviar invitación
- `POST /organizations/{slug}/delete` — Soft delete
- `POST /organizations/{slug}/restore` — Restaurar
- `POST /organizations/{slug}/force-delete` — Eliminación permanente

---

### Módulo Shared

**Responsabilidad**: Infraestructura transversal, planes, tags, Value Objects.

#### Planes Configurables

| Plan | Orgs | Blueprints/Org | Miembros/Org | Variables/BP | API | Marketplace |
|------|------|----------------|--------------|--------------|-----|-------------|
| **Free** | 2 | 3 | 5 | 50 | ❌ | ❌ |
| **Pro** | 5 | 25 | 50 | 150 | ✅ | ✅ |
| **Enterprise** | ∞ | ∞ | ∞ | ∞ | ✅ | ✅ |

Los planes se definen en BD (tabla `plans`), no están hardcodeados. El plan del usuario se hereda a todas sus organizaciones en cascada.

#### Value Objects

| VO | Validación |
|----|-----------|
| `Email` | Formato válido, lowercase automático |
| `Uuid` | UUID v4 válido, generación automática |
| `Slug` | Solo minúsculas/números/guiones, sanitización |

#### Services

| Service | Función |
|---------|---------|
| `PasswordHasher` | Wrapper sobre `password_hash/verify` |
| `UuidGenerator` | Genera instancias de Uuid VO |
| `JsonValidator` | Valida, decodifica, codifica JSON |

---

### Módulo Blueprint

**Responsabilidad**: CRUD de blueprints, variables .env, favoritos, soft delete, tabs dinámicas, resolución de configuración.

#### Modelo de Datos

```
blueprints (id, uuid, organization_id, slug, title, description, is_public, tabs_config JSON, created_by, softDeletes)
blueprint_variables (id, blueprint_id, key, type, default_value, is_interactive, is_secret, section, sort_order)
blueprint_favorites (id, user_id, blueprint_id)
```

#### Tipos de Variables

| Tipo | Comportamiento CLI |
|------|-------------------|
| **Fixed** | Valor predefinido, se inyecta tal cual |
| **Empty** | Se crea la variable sin valor |

Flags adicionales:
- `is_interactive`: El CLI pregunta al usuario por el valor
- `is_secret`: Valor encriptado en BD, solo visible para Owner

#### Tabs Dinámicas (Plugin Architecture)

Cada blueprint puede tener N tabs configurables de 3 tipos, guardadas en `tabs_config` JSON:

| Tab Type | Descripción | Configuración |
|----------|-------------|---------------|
| **VSCode Extensions** | Lista de extensiones recomendadas | Array de strings (`extensions`) |
| **MCP Servers** | Servidores MCP para contexto AI | Array de servidores (`name`, `command`, `args[]`) |
| **AI Context** | Contexto para agentes AI | `segments[]` |

Las tabs se gestionan via `TabManager` Livewire: add/remove/reorder. Comunicación padre-hijo por eventos `tabs-updated`.

#### Actions

| Action | Descripción |
|--------|-------------|
| `CreateBlueprint` | Genera UUID, valida límite de plan |
| `UpdateBlueprint` | Actualiza datos y tabs |
| `DeleteBlueprint` | Soft delete |
| `RestoreBlueprint` | Recupera del soft delete |
| `ToggleFavorite` | Agrega/elimina favorito |
| `TransferBlueprint` | Transfiere blueprint a otra organización |
| `ResolveBlueprint` | Procesa tabs_config y genera outputs estructurados (`TabOutput[]`, `BlueprintOutput`) incluyendo `agent.md` |
| `GenerateEnvTemplate` | Genera archivo `.env` a partir de las variables del blueprint |

#### Livewire Components

| Componente | Descripción |
|------------|-------------|
| `BlueprintCreateForm` | Wizard de creación con variables y tabs |
| `BlueprintEditForm` | Edición completa con sincronización de tabs |
| `TabManager` | Gestión dinámica de tabs (add/remove/reorder/config) |
| `VariableManager` | CRUD de variables .env con secciones y ordenamiento |
| `BlueprintList` | Tabla de blueprints con filtros |
| `CopyToClipboard` | Componente reutilizable para copiar al portapapeles |

#### Policies

| Policy | Owner | Maintainer | Developer |
|--------|-------|------------|-----------|
| Ver blueprint | ✅ | ✅ | ✅ |
| Editar blueprint | ✅ (cualquiera) | ✅ (cualquiera) | ✅ (solo suyo) |
| Eliminar blueprint | ✅ (cualquiera) | ❌ | ❌ |
| Favorito | ✅ | ✅ | ✅ |
| Transferir blueprint | ✅ | ❌ | ❌ |

**Rutas**:
- `GET /blueprints` — Listado
- `GET /blueprints/create` — Crear blueprint
- `GET /blueprints/favorites` — Favoritos
- `GET /blueprints/deleted` — Papelera (soft deleted)
- `GET /blueprints/{uuid}` — Detalle (resuelve tabs y muestra agent.md)
- `GET /blueprints/{uuid}/edit` — Editar
- `POST /blueprints/{uuid}/transfer` — Transferir a otra org
- `POST /blueprints/{uuid}/delete` — Soft delete
- `POST /blueprints/{uuid}/restore` — Restaurar
- `GET /b/{slug}` — Ver blueprint por slug amigable
- `GET /b/u/{uuid}` — Legacy redirect (301 a `/b/{slug}`)

---

### Dashboard

El `/dashboard` es el centro de control:

- **Sin organizaciones**: CTA grande para crear la primera org
- **Con organizaciones**: Grid de tarjetas con stats, botón "Nueva Org" (si el plan lo permite), warning si se alcanzó el límite

## Flujos de Usuario

### Registro → Primera Organización
```
Usuario visita /register
  → Completa formulario Livewire
  → Registro automático con plan Free
  → Redirect a /dashboard
  → Ve CTA "Crear primera organización"
  → Click → /organizations/create
  → Completa formulario
  → Org creada con plan heredado
  → Redirect a /organizations/{slug}
```

### Crear Blueprint
```
En /organizations/{slug} → Click "Nuevo Blueprint"
  → /blueprints/create?org={id}
  → Completa título (slug auto-generado)
  → Valida límite de plan
  → Blueprint creado con UUID único
  → Redirect a /blueprints/{uuid}
```

## Tests

| Suite | Tests | Assertions |
|-------|-------|------------|
| Auth + Onboarding | 35+ | 90+ |
| Blueprint | 65+ | 120+ |
| Organization | 23 | 58 |
| Shared | 34 | 44 |
| Marketplace | 53 | — |
| Feature (cross-module) | 1 | 56 |
| Agent Context | 33+ | 70+ |
| API Tokens | 24 | 67 |
| **Total** | **487** | **1096** |

Cobertura:
- **Unitarios**: Actions, DTOs, ValueObjects, Policies, Model helpers
- **Feature**: Controllers HTTP, flujos completos

## Decisiones Técnicas Clave

### 1. Arquitectura Modular
Cada módulo es autocontenido. Si mañana queremos extraer `Auth` a un package, podemos hacerlo sin refactorizar 40 archivos.

### 2. Actions sobre lógica en Controllers
Los controllers son orquestadores delegan la lógica real a Actions. Esto permite:
- Reutilizar lógica sin depender de HTTP
- Testear unidades de negocio sin simular requests
- Reemplazar la UI (Livewire → API REST) sin tocar el negocio

### 3. Planes en Base de Datos
Los límites de planes no están hardcodeados. Esto permite:
- Añadir nuevos planes sin deploy
- A/B testing de límites
- Herencia cascada (usuario cambia de plan → todas sus orgs se actualizan)

### 4. JSON para Tabs Dinámicas
Las tabs configurables del blueprint (VSCode Extensions, MCP Servers, AI Context) se guardan en `tabs_config` JSON. Esto permite:
- Añadir nuevos tipos de tab sin alterar el schema (solo el enum `TabType`)
- Escalar para Fase 4 (Marketplace)
- Cada tab tiene config estructurada según su tipo
- Plugin architecture: el `TabManager` no conoce los tipos hardcodeados, usa el enum

### 5. Variables Normalizadas + Tabs en JSON
Las variables .env (pestaña 1) están normalizadas (`blueprint_variables` tabla) porque necesitamos:
- Filtrar por tipo (interactivas)
- Buscar por key
- Ordenar

Las pestañas custom son JSON porque son estructuras libres.

### 6. Soft Deletes
Blueprints y Organizations usan soft deletes. Esto permite:
- Recuperación accidental
- Mantener referencias históricas
- Favoritos persisten aunque el blueprint se elimine

## Estado Actual (MVP - Fase 1)

| Feature | Estado |
|---------|--------|
| Auth (login/register/logout) | ✅ Completo |
| API Token Management | ✅ Completo — Sanctum tokens UI, perfil con tabs, plan-gating |
| Planes configurables | ✅ Completo |
| CRUD Organizaciones | ✅ Completo |
| Gestión de miembros (add/invite/roles) | ✅ Completo |
| Límites por plan (orgs/blueprints/miembros/variables) | ✅ Completo |
| Invitaciones por token | ✅ Completo |
| Roles (Owner/Maintainer/Developer) | ✅ Completo |
| CRUD Blueprints | ✅ Completo |
| Variables .env (con secciones y orden) | ✅ Completo |
| Tabs dinámicas (VSCode, MCP, AI Context) | ✅ Completo |
| Resolución de blueprint + `agent.md` | ✅ Completo |
| Transferencia de blueprints entre orgs | ✅ Completo |
| Favoritos | ✅ Completo |
| Soft deletes (blueprints + orgs) | ✅ Completo |
| Papelera / restauración | ✅ Completo |
| Dashboard | ✅ Completo |
| Responsive UI | ✅ Completo |
| Toasts/Notificaciones | ✅ Completo |
| Copy to clipboard | ✅ Completo |
| Collapsible sections en UI | ✅ Completo |
| Tests | ✅ 487 tests, 1096 assertions |
| **Security (OWASP Top 10:2025)** | ✅ Implementado v1.0 (CSP, rate limiting, exception handler, session encrypt, slugs) |
| **AI Agents / Skills config** | ✅ Completo — Segment CRUD con tipos skill/custom/agent |
| **Marketplace** (`is_public`, `has_marketplace_publish`) | ✅ Completo — Módulo Marketplace v1 |
| **Friendly URLs `/b/{slug}`** | ✅ Completo — Slugs con 301 redirects |
| **Show page downloads** | ✅ Completo — Vault fetch, .md/.env downloads |
| **Dashboard polish** | ✅ Completo — 5 UI deliverables |
| **Onboarding wizard** | ✅ Completo — 4-step wizard, skip-all, email banner |

## Próximas Fases

### Fase 2: Wizard de Blueprints (Pulido)
> Estado: ✅ Completo. Tabs dinámicas, variables, templates, preview de `agent.md`, y live preview panel implementados.

### Fase 3: API REST + CLI
> Friendly URLs y downloads completos. Sanctum instalado, marketplace operativo. Queda exponer API y construir CLI.

### Fase 4: Marketplace
> ✅ Completo. Marketplace v1 operativo como módulo independiente.

### Fase 5: Billing
> Pendiente. Sin cambios desde planificación original.

---

## Comandos Útiles

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
```

## Conexión BD (Desarrollo)

| Parámetro | Valor |
|-----------|-------|
| Motor | SQLite 3 |
| Archivo | `database/database.sqlite` |

---

**Documento actualizado**: 2026-06-30  
**Versión**: MVP Completo (web) + Marketplace v1  
**Commits**: 50+ en rama `develop`
