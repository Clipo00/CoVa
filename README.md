# CoVa — The Config Vault

> Zero-latency environment setup for modern developers.

CoVa es una plataforma SaaS desarrollada en **Laravel 13** que centraliza la lógica de configuración de entornos de desarrollo. Permite a equipos crear, compartir y ejecutar **Blueprints** (plantillas de configuración) que automatizan el setup de proyectos desde `git clone` hasta productivo en segundos.

---

## Stack Tecnológico

| Capa | Tecnología |
|------|------------|
| **Framework** | Laravel 13 (PHP 8.3+) |
| **Frontend** | Blade + Livewire 3 + Tailwind CSS |
| **Auth** | Laravel Breeze-like (custom) + Sanctum (listo para API) |
| **BD** | SQLite (dev) / MySQL (prod) |
| **Tests** | PHPUnit 12.5 |
| **Build** | Vite |

---

## Arquitectura

### Monolito Modular

El proyecto sigue una arquitectura de **monolito modular** donde cada dominio de negocio está autocontenido bajo `app/Modules/`:

```
app/Modules/
├── Auth/              # Autenticación y usuarios
├── Organization/      # Organizaciones, roles, invitaciones
├── Blueprint/         # Blueprints, variables, tabs dinámicas, favoritos
├── Marketplace/       # Marketplace público, suscripciones, votación, notificaciones
└── Shared/            # Código transversal (planes, categorías, VO)
```

Cada módulo contiene: **Actions**, **Controllers**, **DTOs**, **Livewire**, **Models**, **Policies**, **Routes**, **Views** y **Tests**.

### Patrones Aplicados

- **Actions**: Casos de uso encapsulados, reutilizables fuera de HTTP
- **DTOs**: Objetos de transferencia entre capas
- **Policies**: Autorización granular por recurso y rol
- **Value Objects**: `Email`, `Uuid`, `Slug` con validación inline

---

## Quickstart

```bash
# 1. Clonar
git clone <repo-url> cova && cd cova

# 2. Dependencias
composer install
npm install && npm run build

# 3. Entorno
cp .env.example .env
php artisan key:generate

# 4. Base de datos
php artisan migrate:fresh --seed

# 5. Servidor
php artisan serve
```

Accedé a `http://localhost:8000` y registrate. El seeder crea planes (Free/Pro/Enterprise) y categorías predefinidas.

---

## Testing

```bash
# Toda la suite
php artisan test

# Con coverage (requiere XDebug/PCOV)
php artisan test --coverage
```

**Estado actual**: 463 tests, 1029 assertions.

---

## Estructura de Documentación

Toda la documentación vive en `docs/`. Si no sabés por dónde empezar, andá a [`docs/README.md`](docs/README.md) que te guía según tu rol.

| Documento | Contenido |
|-----------|-----------|
| [`docs/README.md`](docs/README.md) | **Centro de navegación** — ¿Quién sos? ¿Qué necesitás? |
| [`docs/PROJECT_SUMMARY.md`](docs/PROJECT_SUMMARY.md) | Arquitectura, módulos, decisiones técnicas, estado actual |
| [`docs/FUNCTIONAL.md`](docs/FUNCTIONAL.md) | Especificación funcional y flujos de usuario |
| [`docs/UI_SPECIFICATION.md`](docs/UI_SPECIFICATION.md) | Especificación de interfaz, componentes, decisiones de UX |
| [`docs/ARCHITECTURE.md`](docs/ARCHITECTURE.md) | Arquitectura modular, flujo de request, patrones, cómo agregar módulos |
| [`docs/CONTRIBUTING.md`](docs/CONTRIBUTING.md) | Setup de entorno, convenciones de código, flujo de trabajo Git |
| [`docs/TESTING.md`](docs/TESTING.md) | Pirámide de tests, patrones por capa, cobertura, anti-patrones |
| [`CHANGELOG.md`](CHANGELOG.md) | Historial de cambios por versión (Keep a Changelog) |
| [`docs/FEATURE_HISTORY.md`](docs/FEATURE_HISTORY.md) | Narrativa de evolución, decisiones, lecciones aprendidas |

> Nota: Los documentos marcados como "próximamente" se generan en las siguientes fases del plan de documentación.

---

## Decisiones Técnicas Clave

1. **Módulos autocontenidos**: Se puede extraer `Auth` a un package sin refactorizar 40 archivos.
2. **Lógica en Actions, no en Controllers**: Permite testear negocio sin simular HTTP y reemplazar la UI sin tocar reglas.
3. **Planes en BD**: Límites configurables sin deploy. Herencia en cascada usuario → organizaciones.
4. **Tabs en JSON + Plugin Architecture**: Nuevos tipos de tab sin alterar schema. `TabManager` desacoplado de tipos concretos.
5. **Variables normalizadas**: Tabla propia para filtrar, buscar y ordenar. Tabs en JSON por ser estructuras libres.
6. **Soft Deletes**: Recuperación accidental y referencias históricas preservadas.

---

## Licencia

Proprietary — Todos los derechos reservados.
