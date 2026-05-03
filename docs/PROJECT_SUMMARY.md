# CoVa - The Config Vault

> Zero-latency environment setup for modern developers.

## Visión General

CoVa es una plataforma SaaS desarrollada en Laravel 13 que centraliza la lógica de configuración de entornos de desarrollo. Permite a equipos crear, compartir y ejecutar **Blueprints** (plantillas de configuración) que automatizan el setup de proyectos desde `git clone` hasta productivo en segundos.

## Arquitectura

### Monolito Modular

El proyecto sigue una arquitectura de **monolito modular** donde cada dominio de negocio está autocontenido:

```
app/Modules/
├── Auth/              # Autenticación y usuarios
├── Organization/      # Organizaciones, roles, invitaciones
├── Blueprint/         # Blueprints, variables, favoritos
└── Shared/            # Código transversal (planes, categorías, VO)
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
| `LoginForm` Livewire | Formulario reactivo con validación en tiempo real |
| `RegisterForm` Livewire | Registro con validación y redirect al dashboard |

**Rutas**:
- `GET /login` — Formulario de login
- `GET /register` — Formulario de registro
- `POST /logout` — Cierre de sesión

---

### Módulo Shared

**Responsabilidad**: Infraestructura transversal, planes, categorías, Value Objects.

#### Planes Configurables

| Plan | Orgs | Blueprints/Org | Miembros/Org | Variables/BP | API | Marketplace |
|------|------|----------------|--------------|--------------|-----|-------------|
| **Free** | 2 | 3 | 5 | 20 | ❌ | ❌ |
| **Pro** | 5 | 25 | 50 | 100 | ✅ | ✅ |
| **Enterprise** | ∞ | ∞ | ∞ | ∞ | ✅ | ✅ |

Los planes se definen en BD (tabla `plans`), no están hardcodeados. El plan del usuario se hereda a todas sus organizaciones en cascada.

#### Categorías Globales

8 categorías predefinidas: Laravel, Node.js, Python, DevOps, Frontend, Mobile, Database, Docker.

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
| `InviteUser` | Genera token de invitación con expiración |
| `AcceptInvitation` | Valida token, añade usuario a org |
| `CreateOrganizationUser` | Crea usuario directo por Owner |

#### Middleware

| Middleware | Uso |
|------------|-----|
| `EnsureOrganizationAccess` | Verifica membresía |
| `EnsureRole` | Verifica rol específico |

**Rutas**:
- `GET /organizations` — Listado
- `GET /organizations/create` — Crear org
- `GET /organizations/{slug}` — Detalle de org

---

### Módulo Blueprint

**Responsabilidad**: CRUD de blueprints, variables .env, favoritos, soft delete.

#### Modelo de Datos

```
blueprints (id, uuid, organization_id, category_id, slug, title, description, is_public, tabs_config JSON, created_by, softDeletes)
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

#### Actions

| Action | Descripción |
|--------|-------------|
| `CreateBlueprint` | Genera UUID, valida límite de plan |
| `UpdateBlueprint` | Actualiza datos |
| `DeleteBlueprint` | Soft delete |
| `RestoreBlueprint` | Recupera del soft delete |
| `ToggleFavorite` | Agrega/elimina favorito |

#### Policies

| Policy | Owner | Maintainer | Developer |
|--------|-------|------------|-----------|
| Ver blueprint | ✅ | ✅ | ✅ |
| Editar blueprint | ✅ (cualquiera) | ✅ (cualquiera) | ✅ (solo suyo) |
| Eliminar blueprint | ✅ (cualquiera) | ❌ | ❌ |
| Favorito | ✅ | ✅ | ✅ |

**Rutas**:
- `GET /blueprints` — Listado
- `GET /blueprints/create` — Crear blueprint
- `GET /blueprints/{uuid}` — Detalle
- `GET /blueprints/{uuid}/edit` — Editar
- `GET /blueprints/favorites` — Favoritos

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
| Auth | 9 | 22 |
| Shared | 34 | 44 |
| Organization | 11 | 30 |
| Blueprint | 7 | 16 |
| Roles/Policies | 14 | 22 |
| **Total** | **78** | **134** |

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

### 4. JSON para Pestañas Custom
Las pestañas 2-4 del blueprint (Extensions, AI Context, Post-Install) se guardan en `tabs_config` JSON. Esto permite:
- Añadir nuevas pestañas sin alterar el schema
- Escalar para Fase 4 (Marketplace)
- Cada pestaña es una estructura libre

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
| Planes configurables | ✅ Completo |
| CRUD Organizaciones | ✅ Completo |
| Límites por plan (orgs/blueprints) | ✅ Completo |
| Invitaciones por token | ✅ Completo |
| Roles (Owner/Maintainer/Developer) | ✅ Completo |
| CRUD Blueprints | ✅ Completo |
| Variables .env | ✅ Estructura lista |
| Favoritos | ✅ Completo |
| Soft deletes | ✅ Completo |
| Dashboard | ✅ Completo |
| Responsive UI | ✅ Completo |
| Toasts/Notificaciones | ✅ Completo |
| Copy to clipboard | ✅ Completo |
| Tests | ✅ 78 tests, 134 assertions |

## Próximas Fases

### Fase 2: Wizard de Blueprints (Pestañas Custom)
- Wizard de 4 pasos para crear blueprints
- Editor de variables .env con tabla dinámica
- JSON editors para pestañas 2, 3, 4
- Preview de `agent.md` generado

### Fase 3: API REST + CLI
- Exponer endpoints API con Sanctum
- Autenticación por API tokens
- Endpoint `GET /api/v1/blueprints/{uuid}/download`
- Paquete CLI en Node.js/Python que ejecute `vault fetch <uuid>`

### Fase 4: Marketplace
- Blueprints públicos (`is_public = true`)
- Rating y reviews
- Templates estándar de la comunidad
- Landing page para no autenticados

### Fase 5: Billing
- Integración con Stripe/PayPal
- Suscripciones mensuales/anuales
- Upgrade/downgrade de planes
- Facturación automática

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

**Documento generado**: 2026-05-03  
**Versión**: MVP Fase 1  
**Commits**: 12 en rama `develop`
