# CoVaR — Guía de Contribución

> Setup del entorno, convenciones de código, y cómo agregar features al proyecto.
> Audiencia: Desarrolladores que se suman al equipo o contribuyen al proyecto.

---

## 1. Setup del Entorno

### 1.1 Requisitos

| Software | Versión | Notas |
|----------|---------|-------|
| PHP | 8.3+ | Requerido para `readonly` classes, enums |
| Composer | 2.6+ | Gestión de dependencias PHP |
| Node.js | 20+ | Build de assets frontend |
| SQLite | 3 | Base de datos de desarrollo |
| Git | 2.40+ | Control de versiones |

### 1.2 Instalación Paso a Paso

```bash
# 1. Clonar repo
git clone <repo-url> covar && cd covar

# 2. Dependencias PHP
composer install

# 3. Dependencias JS
npm install

# 4. Compilar assets
npm run build

# 5. Entorno
cp .env.example .env
php artisan key:generate

# 6. Base de datos (SQLite auto-creada)
php artisan migrate:fresh --seed

# 7. Servidor de desarrollo
php artisan serve
```

**Acceso**: `http://localhost:8000`

**Credenciales por defecto** (si creaste usuarios via tinker/factory): No hay usuario seedeado. Registrate vía `/register`.

### 1.3 Estructura del Proyecto

```
covar/
├── app/
│   ├── Models/              # Modelo User (alias, apunta a Auth\Models\User)
│   └── Modules/             # Módulos de negocio (ver ARCHITECTURE.md)
│       ├── Auth/
│       ├── Blueprint/
│       ├── Organization/
│       └── Shared/
├── config/
│   └── modules.php          # Registro de módulos habilitados
├── database/
│   ├── factories/           # Factories Eloquent
│   ├── migrations/          # Migraciones (no por módulo, centralizadas)
│   └── seeders/             # Seeders
├── docs/                    # Documentación del proyecto
├── resources/
│   ├── css/                 # Tailwind + custom
│   ├── js/                  # Vite entry point
│   └── views/               # Vistas globales (layout principal)
├── routes/
│   └── web.php              # Rutas globales (mínimas, la mayoría están en módulos)
├── tests/
│   ├── Feature/             # Tests feature globales (mínimos)
│   └── Unit/                # Tests unitarios globales (mínimos)
└── ...
```

> **Nota**: La mayoría del código vive en `app/Modules/`, no en `app/` raíz. Las rutas globales en `routes/web.php` solo registran el layout principal y redirects.

---

## 2. Convenciones de Código

### 2.1 PHP

- **`declare(strict_types=1);`** al inicio de TODO archivo PHP.
- **Tipado estricto**: Todos los parámetros y retornos deben tener tipo. No usar `mixed` salvo estrictamente necesario.
- **Propiedades tipadas**: Siempre tipar propiedades de clase.
- **Clases `final`**: Por defecto, salvo que necesites herencia.
- **DTOs `readonly`**: Todos los DTOs son `final readonly class`.
- **Enums**: Usar enums PHP 8.1+ para estados, tipos, roles.
- **Namespaces**: `App\Modules\{Module}\{Capa}`.
- **Naming**:
  - Clases: `PascalCase`
  - Métodos/funciones: `camelCase`
  - Variables: `camelCase`
  - Constantes: `UPPER_SNAKE_CASE`
  - Archivos: `PascalCase.php`

### 2.2 Laravel / Eloquent

- **Controllers delgados**: Máximo 5-7 líneas por método. Delegar a Actions.
- **No queries en controllers**: Usar Scopes o Actions.
- **Mass assignment**: Siempre definir `$fillable` o `$guarded` en Models.
- **Relaciones**: Tipar con PHPDoc para autocompletion:
  ```php
  /** @var BelongsTo<Organization, $this> */
  public function organization(): BelongsTo
  ```
- **Migrations**: Usar `index()` en foreign keys y campos de búsqueda frecuente.
- **Seeders**: Un seeder por entidad. `DatabaseSeeder` orquesta el orden.

### 2.3 Livewire

- **Component naming**: `module.path.name` (ej: `blueprint.components.tab-manager`)
- **State público**: Propiedades `public` para binding con Blade.
- **Métodos públicos**: Acciones del usuario (camelCase).
- **Métodos privados**: Helpers internos (camelCase, prefijo descriptivo).
- **Validación**: Usar `$rules` property o `#[Validate]` attributes (Livewire 3).
- **Eventos**: Nombre en kebab-case (`tabs-updated`), payload como array asociativo.

### 2.4 Blade / Tailwind

- **Componentes Livewire**: Usar `<livewire:module.component-name />`
- **Vistas de módulo**: `view('module::path.view')`
- **Tailwind**: Utility-first. Evitar CSS custom salvo para animaciones complejas.
- **Responsive**: Mobile-first (`sm:`, `md:`, `lg:`).

### 2.5 Commits

Usar **Conventional Commits**:

```
<type>(<scope>): <description>

[optional body]

[optional footer]
```

**Tipos**:
| Tipo | Uso | Ejemplo |
|------|-----|---------|
| `feat` | Nueva feature | `feat(blueprint): add MCP server tab` |
| `fix` | Bug fix | `fix(auth): password hash comparison` |
| `docs` | Documentación | `docs: update architecture diagram` |
| `refactor` | Refactor sin cambio de comportamiento | `refactor(organization): extract validation to action` |
| `test` | Tests | `test(blueprint): add tab manager coverage` |
| `chore` | Tareas de mantenimiento | `chore: update dependencies` |

**Scopes comunes**: `auth`, `organization`, `blueprint`, `shared`, `ui`, `test`

**Reglas**:
- Descripción en imperativo ("add" no "added" ni "adding")
- Línea de subject máx 72 caracteres
- Body opcional para explicar el "por qué"

---

## 3. Cómo Agregar una Feature

### 3.1 Agregar un Campo a un Modelo Existente

1. **Crear migración**:
   ```bash
   php artisan make:migration add_field_to_blueprints_table
   ```

2. **Actualizar Model**:
   - Añadir a `$fillable`
   - Añadir a `$casts` si aplica

3. **Actualizar DTO** (si el campo se usa en creación/actualización):
   - Añadir propiedad al DTO

4. **Actualizar Action**:
   - Incluir campo en `create()` o `update()`

5. **Actualizar Formulario Livewire**:
   - Añadir propiedad pública
   - Añadir regla de validación

6. **Actualizar Vista**:
   - Input o display del campo

7. **Tests**:
   - Feature test: enviar campo, verificar persistencia
   - Unit test: validación de DTO/Action si hay lógica

### 3.2 Agregar una Nueva Action

1. **Crear clase** en `app/Modules/{Module}/Actions/{ActionName}.php`:
   ```php
   <?php
   declare(strict_types=1);
   namespace App\Modules\{Module}\Actions;

   final class NewAction
   {
       public function execute(NewActionData $data): ResultType
       {
           // Lógica pura, no HTTP
       }
   }
   ```

2. **Inyectar en Controller** (method injection) o constructor si se usa en múltiples métodos.

3. **Tests unitarios** en `app/Modules/{Module}/Tests/Unit/Actions/NewActionTest.php`:
   ```php
   class NewActionTest extends TestCase
   {
       use RefreshDatabase;

       public function test_executes_successfully(): void
       {
           // Arrange
           $action = app(NewAction::class);
           $data = new NewActionData(...);

           // Act
           $result = $action->execute($data);

           // Assert
           $this->assertInstanceOf(ResultType::class, $result);
       }
   }
   ```

### 3.3 Agregar un Nuevo Módulo

Ver paso a paso en [`ARCHITECTURE.md`](ARCHITECTURE.md#34-agregar-un-nuevo-módulo-paso-a-paso).

### 3.4 Agregar un Componente Livewire

1. **Crear clase** en `app/Modules/{Module}/Livewire/{Carpeta}/{ComponentName}.php`

2. **Registrar en ServiceProvider** del módulo:
   ```php
   Livewire::component('module.path.component-name', ComponentName::class);
   ```

3. **Crear vista** en `app/Modules/{Module}/Views/livewire/{carpeta}/{component-name}.blade.php`

4. **Usar en Blade**:
   ```blade
   <livewire:module.path.component-name />
   ```

---

## 4. Testing

> Para estrategia detallada de testing, patrones por capa, cobertura y anti-patrones, ver [`TESTING.md`](TESTING.md).

### 4.1 Correr Tests

```bash
# Toda la suite
php artisan test

# Solo unitarios
php artisan test --testsuite=Unit

# Solo feature
php artisan test --testsuite=Feature

# Un archivo específico
php artisan test app/Modules/Blueprint/Tests/Unit/Actions/CreateBlueprintTest.php

# Con coverage (requiere XDebug o PCOV)
php artisan test --coverage
```

### 4.2 Estructura de Tests

Los tests viven en `app/Modules/{Module}/Tests/`:

```
Tests/
├── Feature/              # Tests HTTP, flujos completos
│   ├── AuthControllerTest.php
│   └── ...
└── Unit/                 # Tests unitarios por capa
    ├── Actions/
    ├── Models/
    ├── Policies/
    └── ...
```

Ver [`TESTING.md`](TESTING.md) para estrategia detallada.

### 4.3 Fixtures y Factories

**Factory disponible**:
- `UserFactory` — crea usuarios con password hasheado

**Crear nuevas factories** (si aplica):
```bash
php artisan make:factory OrganizationFactory
```

**Uso en tests**:
```php
$user = User::factory()->create();
$org = Organization::factory()->create(['owner_id' => $user->id]);
```

> Nota: No todas las entidades tienen factory todavía. Si necesitás una, creala.

### 4.4 Cobertura Mínima Esperada

| Capa | Cobertura esperada |
|------|-------------------|
| Actions | 100% (lógica de negocio crítica) |
| Policies | 100% (seguridad) |
| DTOs / VO | 100% (validación) |
| Livewire Forms | 80%+ (flujos principales) |
| Controllers | 70%+ (orquestación) |
| Models (scopes) | 60%+ |

---

## 5. Flujo de Trabajo (Git)

### 5.1 Branches

- `main` / `master`: Producción estable
- `develop`: Integración continua
- `feature/nombre-descriptivo`: Nuevas features
- `fix/nombre-descriptivo`: Bug fixes

### 5.2 Pull Requests

1. Crear branch desde `develop`
2. Commits con conventional commits
3. Asegurar que tests pasan: `php artisan test`
4. Crear PR a `develop`
5. Revisión obligatoria (mínimo 1 aprobador)
6. Merge con squash (opcional, según preferencia del equipo)

### 5.3 Pre-commit Checklist

```bash
# Antes de commitear, correr:
php artisan test
```

Si hay tests fallidos, NO commitear (excepto si estás en medio de un refactor y commiteas WIP con `[WIP]` en el mensaje).

---

## 6. Troubleshooting

### 6.1 "Class not found" al agregar un módulo
- Verificar que el ServiceProvider existe y está correctamente nombrado: `{Module}ServiceProvider`
- Verificar que está en `config/modules.php`
- Correr `composer dump-autoload`

### 6.2 Rutas de módulo no funcionan
- Verificar que el archivo `app/Modules/{Module}/Routes/web.php` existe
- Verificar namespace del controller en la ruta
- Verificar que no hay conflicto de nombres con rutas globales

### 6.3 Views no se encuentran
- Verificar que el ServiceProvider del módulo llama a `$this->loadViewsFrom(...)`
- Verificar namespace: `module::view` (lowercase del nombre de módulo)

### 6.4 Livewire component no renderiza
- Verificar registro en ServiceProvider: `Livewire::component('module.name', Class::class)`
- Verificar nombre en Blade: `<livewire:module.name />`
- Verificar que la clase existe y no tiene errores de sintaxis

### 6.5 Tests fallan en SQLite
- Verificar que `phpunit.xml` tiene `DB_DATABASE=:memory:`
- Verificar que no hay constraints de FK que fallen (usar `$table->foreignId(...)->constrained()->cascadeOnDelete()`)

---

## 7. Contacto y Recursos

- **Documentación**: Todo en `docs/`
- **Arquitectura**: [`ARCHITECTURE.md`](ARCHITECTURE.md)
- **Testing**: [`TESTING.md`](TESTING.md)
- **Funcional**: [`FUNCTIONAL.md`](FUNCTIONAL.md)

---

**Documento generado**: 2026-05-15  
**Versión**: 1.0  
**Última actualización**: Fase 3 del plan de documentación
