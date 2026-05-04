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
| **SDD workflow (spec-driven)** | `sdd-*` (auto-detectado por prefijo) |

### Skill Auto-Detection Rules

1. **Por nombre de archivo/directorio**:
   - `Actions/*` → cargar `covar-laravel-action`
   - `Livewire/*` → cargar `covar-laravel-livewire`
   - `Policies/*` → cargar `covar-laravel-policy`
   - `Models/*` → cargar `covar-laravel-model`
   - `Controllers/*` → cargar `covar-laravel-controller`
   - `Tests/*` → cargar `covar-laravel-test`

2. **Por extensión/comportamiento**:
   - extiende `Livewire\Component` → `covar-laravel-livewire`
   - implements authorization logic → `covar-laravel-policy`
   - tiene método `execute()` → `covar-laravel-action`

3. **Por operaciones específicas**:
   - `create_blueprint` / `CreateBlueprint` → `covar-laravel-action` (Blueprint context)
   - `authenticate` / `LoginUser` → `covar-laravel-action` (Auth context)
   - soft deletes → `covar-laravel-model`

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
| **Free** | 2 | 3 | 5 | 20 | ❌ | ❌ |
| **Pro** | 5 | 25 | 50 | 100 | ✅ | ✅ |
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

## Resources

- **Project Summary**: [docs/PROJECT_SUMMARY.md](docs/PROJECT_SUMMARY.md)
- **Laravel Docs**: https://laravel.com/docs/13.x
- **Livewire Docs**: https://livewire.laravolt.dev/docs/
- **Tailwind CSS**: https://tailwindcss.com/