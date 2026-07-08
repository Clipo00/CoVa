# CoVaR — The Config Vault Recovery

> Zero-latency environment setup for modern developers.

CoVaR es una plataforma SaaS desarrollada en **Laravel 13** que centraliza la lógica de configuración de entornos de desarrollo. Permite a equipos crear, compartir y ejecutar **Blueprints** (plantillas de configuración) que automatizan el setup de proyectos desde `git clone` hasta productivo en segundos, con un solo comando: `covar vault:fetch <slug>`.

---

## Descripción General

### El problema

Los equipos de desarrollo pierden horas configurando entornos. Cada nuevo miembro copia `.env` por Slack, instala extensiones a mano, y reza para que el proyecto compile. El conocimiento de configuración vive en cabezas, no en herramientas.

### La solución

CoVaR convierte ese conocimiento en **Blueprints**: plantillas autocontenidas que combinan variables de entorno, extensiones de VSCode, servidores MCP, y contexto para agentes de IA. Un blueprint se define una vez y se ejecuta desde la terminal con un solo comando.

### ¿Para quién es?

- **Leads técnicos** que quieren reducir el tiempo de onboarding de días a minutos.
- **Equipos** que necesitan consistencia entre entornos de desarrollo.
- **Desarrolladores** que quieren compartir sus configuraciones con la comunidad.

---

## Stack Tecnológico

| Capa | Tecnología | Versión |
|------|------------|---------|
| **Lenguaje** | PHP | 8.4+ |
| **Framework** | Laravel | 13.x |
| **Frontend reactivo** | Livewire | 4.x |
| **Estilos** | Tailwind CSS | 4.x |
| **Build** | Vite | 8.x |
| **Autenticación** | Laravel Breeze (custom) + Sanctum | Sanctum 4.x |
| **API** | Laravel Sanctum (REST JSON) | 4.x |
| **Base de datos** | SQLite (dev) / MySQL (prod) | SQLite 3 / MySQL 8.0+ |
| **Testing unitario/feature** | PHPUnit | 12.5 |
| **Testing E2E** | Playwright | 1.60 |
| **CLI** | Laravel Zero | 2.0 |
| **i18n** | Laravel Localization (`lang/`) | ES + EN (339+ claves) |
| **Validación de email** | Disposable Email Guard | 2.x |

---

## Instalación y Ejecución

### Requisitos previos

| Software | Versión mínima | Notas |
|----------|---------------|-------|
| PHP | 8.4 | Extensiones: `pdo_sqlite`, `mbstring`, `fileinfo` |
| Composer | 2.6+ | Gestión de dependencias PHP |
| Node.js | 20+ | Build de assets frontend |
| SQLite | 3 | Base de datos de desarrollo (sin instalación extra) |
| Git | 2.40+ | Control de versiones |

### Paso a paso

```bash
# 1. Clonar
git clone <repo-url> covar && cd covar

# 2. Dependencias PHP
composer install

# 3. Dependencias JS y assets
npm install && npm run build

# 4. Configurar entorno
cp .env.example .env
php artisan key:generate

# 5. Base de datos (SQLite se crea automáticamente)
php artisan migrate:fresh --seed

# 6. Servidor de desarrollo
php artisan serve
```

Accede a `http://localhost:8000` y regístrate. El seeder crea:

- **Planes**: Free (2 orgs, 3 BP/org, 5 miembros, 50 variables), Pro (5 orgs, 25 BP, 50 miembros, 150 variables), Enterprise (ilimitado).
- **Categorías**: 8 categorías predefinidas para clasificar blueprints.
- **Organización de marketplace**: `covar-marketplace` como repositorio público.

### Desarrollo con hot reload

```bash
# Servidor + Vite + logs en una terminal
composer run dev
```

---

## Estructura del Proyecto

```
covar/
├── app/
│   ├── Models/                      # Modelo User (alias a Auth\Models\User)
│   └── Modules/                     # Módulos de dominio autocontenidos
│       ├── Auth/                    # Registro, login, logout, API tokens
│       ├── Blueprint/               # CRUD de blueprints, variables, tabs, favoritos
│       ├── Dashboard/               # Centro de control del usuario
│       ├── Marketplace/             # Publicación, votación, suscripciones, notificaciones
│       ├── Organization/            # Organizaciones, roles, miembros, invitaciones
│       └── Shared/                  # Planes, categorías, Value Objects, servicios
├── cli/                             # Herramienta CLI (Laravel Zero)
│   ├── app/Commands/                # Comandos: fetch, list, config
│   ├── README.md                    # Guía de instalación y uso del CLI
│   └── BUILD.md                     # Instrucciones de build del PHAR
├── config/
│   └── modules.php                  # Registro de módulos habilitados
├── database/
│   ├── factories/                   # Factories Eloquent
│   ├── migrations/                  # Migraciones de base de datos
│   └── seeders/                     # Seeders de datos iniciales
├── docs/                            # Documentación completa del proyecto
│   ├── README.md                    # Centro de navegación de la documentación
│   ├── PROJECT_SUMMARY.md           # Arquitectura, módulos, estado actual
│   ├── FUNCTIONAL.md                # Especificación funcional y reglas de negocio
│   ├── UI_SPECIFICATION.md          # Pantallas, componentes, decisiones UX
│   ├── ARCHITECTURE.md              # Patrones, flujo de request, decisiones técnicas
│   ├── CONTRIBUTING.md              # Setup, convenciones, cómo agregar features
│   ├── TESTING.md                   # Pirámide de tests, patrones, cobertura
│   └── FEATURE_HISTORY.md           # Narrativa de evolución y lecciones aprendidas
├── lang/
│   ├── es/                          # Traducciones en castellano (España)
│   └── en/                          # Traducciones en inglés
├── openspec/                        # Especificaciones SDD de los cambios
├── resources/
│   ├── css/                         # Tailwind + estilos custom
│   ├── js/                          # Entry point de Vite
│   └── views/                       # Vistas globales (layouts, landing, componentes)
├── routes/
│   ├── web.php                      # Rutas globales (mínimas)
│   └── api.php                      # API REST con Sanctum (auth:sanctum)
├── tests/
│   ├── Feature/                     # Tests feature globales
│   ├── Unit/                        # Tests unitarios globales
│   └── e2e/                         # Tests E2E con Playwright
├── playwright.config.ts             # Configuración de Playwright
├── phpunit.xml                      # Configuración de PHPUnit
├── vite.config.js                   # Configuración de Vite
└── CHANGELOG.md                     # Historial de cambios por versión
```

### Estructura de un módulo

Cada módulo bajo `app/Modules/{Modulo}/` sigue esta organización:

```
{Modulo}/
├── Actions/              # Casos de uso encapsulados
├── Controllers/          # Orquestadores HTTP (delgados)
├── DTOs/                 # Objetos de transferencia tipados
├── Enums/                # Enumeraciones PHP 8.1+
├── Livewire/
│   ├── Components/       # Componentes reutilizables
│   ├── Forms/            # Formularios principales
│   └── Tables/           # Tablas de datos
├── Middleware/           # Middleware específico del dominio
├── Models/               # Entidades Eloquent
├── Policies/             # Autorización por recurso y rol
├── Providers/            # ServiceProvider del módulo
├── Routes/               # Rutas web del módulo
├── Tests/
│   ├── Feature/          # Tests HTTP y flujos completos
│   └── Unit/             # Tests unitarios por capa
└── Views/                # Plantillas Blade y vistas Livewire
```

---

## Funcionalidades Principales

### Autenticación y Seguridad

- Registro y login con validación en tiempo real (Livewire)
- Verificación de email con URLs firmadas (24h de expiración)
- MFA opcional con código de 6 dígitos por email (single-use, 10min)
- Bloqueo de emails desechables/temporales en registro
- Rate limiting en rutas sensibles (login, MFA, API)
- CSP, headers de seguridad, encrypt de sesión, excepciones sin stack traces
- Verificación OWASP Top 10:2025 implementada

### Organizaciones y Colaboración

- CRUD completo de organizaciones con slugs amigables
- Roles por organización: Owner, Maintainer, Developer
- Invitaciones por email con token único (48h de expiración)
- Gestión de miembros (añadir, cambiar rol, eliminar)
- Soft deletes con restauración y force delete
- Límites configurables por plan (orgs, blueprints, miembros, variables)

### Blueprints (núcleo del producto)

- CRUD completo con UUID v4 inmutable y slugs legibles (`/b/{slug}`)
- **Variables de entorno**: Fixed/Empty, interactivas, secretas, agrupadas por secciones con colores
- **Tabs dinámicas** (Plugin Architecture):
  - VSCode Extensions: lista de extensiones recomendadas
  - MCP Servers: servidores de contexto para agentes de IA
  - AI Context: segmentos ordenados (skills, custom, agent) que generan `agent.md`
- **Resolución de blueprint**: genera outputs estructurados y archivos descargables
- Plantillas predefinidas (Laravel, Node.js, Python)
- Live preview en creación/edición (debounce 300ms)
- Favoritos con toggle instantáneo
- Transferencia de blueprints entre organizaciones
- Soft deletes con papelera y restauración
- Filtros avanzados por organización, categoría, tags y búsqueda
- Descargas: `.env`, `agent.md`, archivos `.md` por segmento

### Marketplace

- Listado público con búsqueda, filtros por tags, y ordenamiento (rating, popularidad, reciente)
- Publicación de blueprints con toggle `is_public`
- Suscripciones (fork a organización del usuario)
- Sistema de votación (upvote/downvote, un voto por usuario)
- Notificaciones in-app (campanita, buzón, badge en nav)
- Sincronización de blueprints publicados (re-publicar cambios)
- Desvinculación limpia al eliminar blueprint publicado

### Dashboard y Onboarding

- Centro de control con estadísticas (orgs, blueprints, marketplace)
- Tarjetas de organizaciones con roles y contadores
- Onboarding wizard de 4 pasos post-registro (saltable)
- Banner de verificación de email no bloqueante
- Lista de blueprints recientes con badges de categoría

### API REST y CLI

- API JSON con autenticación Sanctum (`auth:sanctum`)
- Rate limiting (60 req/min) y plan-gating (Free bloqueado)
- RFC 7807 error responses para rutas `api/*`
- Endpoints: `GET /api/blueprints`, `GET /api/blueprints/{slug}`, `GET /api/me`, `POST /api/fetch/{slug}/verify`
- **CLI tool** (`covar`): PHAR autocontenido (~11.5 MB)
  - `covar config:set-key` — configura API key con validación
  - `covar vault:list` — lista blueprints accesibles con tabla formateada
  - `covar vault:fetch <slug>` — scaffold completo: `.agent.md`, `.vscode/`, `.env` con variables resueltas
  - Secret double-auth flow: desencripta variables secretas con contraseña

### Gestión de API Tokens

- CRUD de tokens personales Sanctum desde el perfil de usuario
- Plan-gating: solo Pro/Enterprise pueden crear tokens
- Expiración obligatoria (máx 1 año)
- Confirmación de contraseña para crear y revocar
- Token en texto plano visible una sola vez
- Rate limiting: 10 operaciones por minuto

### UI/UX

- Diseño responsive (mobile-first)
- Modo oscuro completo con toggle animado (persistencia en localStorage)
- Sistema de toasts con auto-dismiss (éxito, error, warning)
- Modal de confirmación Alpine.js con backdrop blur
- Landing page con terminal animada, demo carousel, y sección de precios
- Copy to clipboard en toda la aplicación
- Secciones colapsables en blueprint show
- Selector de idioma ES/EN con persistencia en BD + cookie
- Spinners en botones de submit (login, registro, creación)

### Testing

- **Unitarios**: Actions, Policies, Value Objects, Services, Tabs
- **Feature**: Controllers HTTP, flujos completos, validación, autorización
- **E2E**: Playwright sobre Chromium (auth, navegación, perfil)
- **Estado actual**: 487 tests, 1096 assertions
- Cobertura objetivo: 100% Actions/Policies/VO, 85%+ Controllers, 80%+ Livewire

---

## CLI (`covar`) — Instalación rápida

```bash
# Descargar PHAR
curl -L -o covar https://covar.dev/downloads/covar.phar
chmod +x covar
sudo mv covar /usr/local/bin/

# Configurar API key (se obtiene en el perfil de CoVaR)
covar config:set-key cova_tu_token_aqui

# Listar tus blueprints
covar vault:list

# Scaffoldear un blueprint completo
covar vault:fetch laravel-api-starter
```

Ver [`cli/README.md`](cli/README.md) para la guía completa de instalación y uso.

---

## Testing

```bash
# Suite completa (PHPUnit)
php artisan test

# Con coverage (requiere XDebug o PCOV)
php artisan test --coverage

# Solo tests unitarios
php artisan test --testsuite=Unit

# Solo tests feature
php artisan test --testsuite=Feature

# Tests E2E (Playwright)
npm run test:e2e
npm run test:e2e:ui      # con interfaz visual
npm run test:e2e:headed   # con navegador visible
```

**Estado actual**: 487 tests, 1096 assertions. Ver [`docs/TESTING.md`](docs/TESTING.md) para la estrategia completa.

---

## Documentación

Toda la documentación vive en `docs/`. Si no sabes por dónde empezar, [`docs/README.md`](docs/README.md) te guía según tu rol:

| Documento | Contenido |
|-----------|-----------|
| [`docs/README.md`](docs/README.md) | **Centro de navegación** — ¿Quién eres? ¿Qué necesitas? |
| [`docs/PROJECT_SUMMARY.md`](docs/PROJECT_SUMMARY.md) | Arquitectura, módulos, decisiones técnicas, estado actual |
| [`docs/FUNCTIONAL.md`](docs/FUNCTIONAL.md) | Especificación funcional y flujos de usuario |
| [`docs/UI_SPECIFICATION.md`](docs/UI_SPECIFICATION.md) | Especificación de interfaz, componentes, decisiones de UX |
| [`docs/ARCHITECTURE.md`](docs/ARCHITECTURE.md) | Arquitectura modular, flujo de request, patrones, cómo agregar módulos |
| [`docs/CONTRIBUTING.md`](docs/CONTRIBUTING.md) | Setup de entorno, convenciones de código, flujo de trabajo Git |
| [`docs/TESTING.md`](docs/TESTING.md) | Pirámide de tests, patrones por capa, cobertura, anti-patrones |
| [`CHANGELOG.md`](CHANGELOG.md) | Historial de cambios por versión (Keep a Changelog) |
| [`docs/FEATURE_HISTORY.md`](docs/FEATURE_HISTORY.md) | Narrativa de evolución, decisiones, lecciones aprendidas |
| [`cli/README.md`](cli/README.md) | Instalación y uso del CLI `covar` |

---

## Credenciales de Prueba

El seeder `TestUserSeeder` crea dos usuarios listos para probar la aplicación:

| Email | Contraseña | Plan | Notas |
|-------|-----------|------|-------|
| `admin@covar.dev` | `password` | Free | Email verificado, onboarding completado |
| `pro@covar.dev` | `password` | Pro | Email verificado, onboarding completado, acceso a API tokens y marketplace |

> Si despliegas desde cero, ejecuta `php artisan migrate:fresh --seed` para crear estos usuarios automáticamente.

---

## Despliegue

**URL de producción**: [PENDIENTE — Añadir tras desplegar en Railway]

La aplicación está configurada para desplegar en [Railway](https://railway.app) con Railpack (detección automática de Laravel):

- `railway.toml` — Configuración de build y start
- `scripts/railway-build.sh` — Compilación de assets y CLI PHAR
- `scripts/railway-start.sh` — Migraciones, seeders, y generación de PHAR

Ver [`railway.toml`](railway.toml) para los detalles de configuración.

---

## TFM — Recursos de Evaluación

| Recurso | Enlace |
|---------|--------|
| **Presentación (Slides)** | [PENDIENTE — Añadir enlace a Google Slides] |
| **Vídeo de demostración** | [PENDIENTE — Añadir enlace a YouTube/Drive] |

---

## Licencia

Proprietary — Todos los derechos reservados.
