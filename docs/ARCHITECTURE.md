# CoVa — Arquitectura del Proyecto

> Documento de arquitectura técnica, patrones, y guía de módulos.
> Audiencia: Desarrolladores nuevos en el proyecto y arquitectos.

---

## 1. Visión de Arquitectura

CoVa es un **monolito modular** sobre Laravel 13. Cada dominio de negocio está autocontenido en un módulo bajo `app/Modules/`. La meta es que cualquier módulo se pueda extraer a un package independiente sin refactorizar 40 archivos.

**Principios guía**:
1. **Un módulo, un dominio**: Auth maneja identidad. Blueprint maneja plantillas. No mezclar.
2. **Controllers orquestan, Actions ejecutan**: La lógica de negocio vive en Actions, no en Controllers ni Livewire.
3. **Dependencias unidireccionales**: Módulos superiores dependen de inferiores. Shared no depende de nadie. Auth no depende de Blueprint.
4. **Configuración sobre convención**: Módulos se registran en `config/modules.php`. No hay auto-discovery mágico de carpetas.

---

## 2. Diagrama de Módulos y Dependencias

```
                    ┌─────────────┐
                    │   Guest     │
                    │  (no auth)  │
                    └──────┬──────┘
                           │
        ┌──────────────────┼──────────────────┐
        │                  │                  │
        ▼                  ▼                  ▼
   ┌─────────┐      ┌──────────┐       ┌──────────┐      ┌─────────────┐
   │  Auth   │◄────►│Organization│◄────►│ Blueprint │      │ Marketplace │
   │ (base)  │      │ (tenancy)  │       │ (core)    │      │  (public)  │
   └────┬────┘      └─────┬────┘       └─────┬─────┘      └──────┬──────┘
        │                 │                  │                   │
        │                 │                  │                   │
        └─────────────────┼──────────────────┼───────────────────┘
                          │                  │
                          │                  │
                          └────────┬─────────┘
                                   │
                                   ▼
                            ┌────────────┐
                            │   Shared   │
                            │(infra, VO) │
                             └────────────┘
```

**Módulos activos**:

```
app/Modules/
├── Auth/
├── Organization/
├── Blueprint/
├── Marketplace/       # Marketplace público, suscripciones, votación, notificaciones
└── Shared/
```

**Reglas de dependencia**:
- **Shared**: No depende de nadie. Todos dependen de Shared.
- **Auth**: Depende de Shared. No depende de Organization ni Blueprint.
- **Organization**: Depende de Auth (User) y Shared (Plan, VO).
- **Blueprint**: Depende de Auth (User), Organization (org, roles), y Shared (Plan, Category, VO).
- **Marketplace**: Depende de Auth (User, votos, suscripciones) y Shared (Plan, VO, notificaciones).
- **Nunca**: Auth → Blueprint. Organization no conoce Blueprint (solo via relaciones Eloquent, no lógica).

---

## 3. Sistema de Módulos

> Para setup del entorno, convenciones de código, y troubleshooting, ver [`CONTRIBUTING.md`](CONTRIBUTING.md).

### 3.1 Registro Automático

El registro centralizado está en `config/modules.php`:

```php
return [
    'enabled' => [
        'Auth',
        'Organization',
        'Blueprint',
        'Marketplace',
        'Shared',
    ],
];
```

**Flujo de boot**:
1. Laravel carga `ModuleServiceProvider` (registrado en `config/app.php`)
2. `ModuleServiceProvider::register()` itera `config('modules.enabled')`
3. Por cada módulo, busca y registra `{Module}ServiceProvider` en `app/Modules/{Module}/Providers/`
4. `RouteServiceProvider::boot()` carga rutas de cada módulo desde `app/Modules/{Module}/Routes/web.php`
5. Cada `{Module}ServiceProvider` registra:
   - Views: `$this->loadViewsFrom(__DIR__ . '/../Views', 'module')`
   - Livewire components: `Livewire::component('module.component-name', Class::class)`

### 3.2 Estructura de un Módulo

```
app/Modules/{Module}/
├── Actions/              # Casos de uso / comandos
├── Controllers/          # Orquestación HTTP (thin controllers)
├── DTOs/                 # Objetos de transferencia
├── Enums/                # Enums PHP 8.1+ (si aplica)
├── Exceptions/           # Excepciones custom del dominio
├── Livewire/
│   ├── Components/       # Componentes reutilizables
│   ├── Concerns/         # Traits de comportamiento Livewire
│   ├── Forms/            # Formularios principales
│   └── Tables/           # Tablas de datos
├── Middleware/           # Middleware específico del módulo
├── Models/               # Entidades Eloquent
├── Policies/             # Autorización
├── Providers/
│   └── {Module}ServiceProvider.php   # Registro de views, livewire, bindings
├── Requests/             # Form Requests (validación HTTP)
├── Routes/
│   └── web.php           # Rutas del módulo
├── Tabs/                 # Plugins de tabs (solo Blueprint)
├── Tests/
│   ├── Feature/          # Tests HTTP/flujos completos
│   └── Unit/             # Tests unitarios por capa
│       ├── Actions/
│       ├── Models/
│       ├── Policies/
│       └── ...
└── Views/
    ├── livewire/
    │   ├── components/
    │   ├── forms/
    │   └── tables/
    └── *.blade.php       # Vistas de página
```

### 3.3 Convenciones de Namespace

| Elemento | Namespace | Ejemplo |
|----------|-----------|---------|
| Action | `App\Modules\{Module}\Actions` | `App\Modules\Blueprint\Actions\CreateBlueprint` |
| Controller | `App\Modules\{Module}\Controllers` | `App\Modules\Blueprint\Controllers\BlueprintController` |
| DTO | `App\Modules\{Module}\DTOs` | `App\Modules\Auth\DTOs\RegisterUserData` |
| Livewire Form | `App\Modules\{Module}\Livewire\Forms` | `App\Modules\Auth\Livewire\Forms\LoginForm` |
| Livewire Component | `App\Modules\{Module}\Livewire\Components` | `App\Modules\Blueprint\Livewire\Components\TabManager` |
| Model | `App\Modules\{Module}\Models` | `App\Modules\Auth\Models\User` |
| Policy | `App\Modules\{Module}\Policies` | `App\Modules\Blueprint\Policies\BlueprintPolicy` |
| Request | `App\Modules\{Module}\Requests` | `App\Modules\Auth\Requests\LoginRequest` |
| View namespace | `{module}` (lowercase) | `auth::login`, `blueprint::show` |
| Livewire name | `{module}.{path}.{name}` | `auth.forms.login-form`, `blueprint.components.tab-manager` |

### 3.4 Agregar un Nuevo Módulo (Paso a Paso)

1. **Crear estructura de carpetas**:
   ```bash
   mkdir -p app/Modules/{NuevoModulo}/{Actions,Controllers,DTOs,Livewire/Forms,Models,Policies,Providers,Requests,Routes,Tests/Feature,Tests/Unit,Views}
   ```

2. **Crear ServiceProvider** (`app/Modules/{NuevoModulo}/Providers/{NuevoModulo}ServiceProvider.php`):
   ```php
   <?php
   declare(strict_types=1);
   namespace App\Modules\{NuevoModulo}\Providers;
   use Illuminate\Support\ServiceProvider;
   class NuevoModuloServiceProvider extends ServiceProvider
   {
       public function register(): void {}
       public function boot(): void
       {
           $this->loadViewsFrom(__DIR__ . '/../Views', 'nuevomodulo');
           // Registrar Livewire components si aplica
       }
   }
   ```

3. **Agregar a `config/modules.php`**:
   ```php
   'enabled' => [
       'Auth',
       'Organization',
       'Blueprint',
       'Shared',
       'NuevoModulo',  // <-- aquí
   ],
   ```

4. **Crear rutas** (`app/Modules/{NuevoModulo}/Routes/web.php`):
   ```php
   use App\Modules\{NuevoModulo}\Controllers\{NuevoModulo}Controller;
   use Illuminate\Support\Facades\Route;

   Route::middleware('auth')->group(function () {
       Route::get('/nuevo-modulo', [{NuevoModulo}Controller::class, 'index'])->name('nuevo-modulo.index');
   });
   ```

5. **No tocar `RouteServiceProvider` ni `ModuleServiceProvider`**: El auto-discovery funciona solo con el paso 3.

---

## 4. Flujo de Request

```
HTTP Request
    │
    ▼
Route (web.php del módulo)
    │
    ▼
Middleware (auth, EnsureOrganizationAccess, EnsureRole)
    │
    ▼
Controller (orquesta, valida permisos via Policy)
    │
    ├──► Form Request (validación de input)
    │
    ▼
Action (lógica de negocio pura)
    │
    ├──► DTO (transporta datos tipados)
    │
    ├──► Value Object (validación de dominio)
    │
    ├──► Model (persistencia Eloquent)
    │
    └──► Service (lógica transversal: hashing, UUID, etc.)
    │
    ▼
Controller (recibe resultado, decide response)
    │
    ├──► View + Livewire (respuesta HTML)
    │
    └──► Redirect (POST-redirect-GET)
```

### 4.1 Controllers: Orquestadores Delgados

Un Controller NUNCA debe contener lógica de negocio. Solo:
- Recibir el Request
- Autorizar (via Policy o middleware)
- Delegar a Action
- Decidir response (View o Redirect)

```php
// BIEN
class BlueprintController
{
    public function show(string $uuid, ResolveBlueprint $resolveBlueprint): View
    {
        $blueprint = Blueprint::where('uuid', $uuid)->firstOrFail();
        $output = $resolveBlueprint->execute($blueprint);  // Delegar

        return view('blueprint::show', [
            'blueprint' => $blueprint,
            'blueprintOutput' => $output,
        ]);
    }
}

// MAL (lógica de negocio en controller)
public function show(string $uuid): View
{
    $blueprint = Blueprint::where('uuid', $uuid)->firstOrFail();
    // 50 líneas de lógica para resolver tabs... NO
}
```

### 4.2 Actions: Casos de Uso

Cada Action es una clase invocable con un único método público (generalmente `execute()`):

```php
final class CreateBlueprint
{
    public function __construct(
        private PlanLimitChecker $limitChecker,
        private UuidGenerator $uuidGenerator,
    ) {}

    public function execute(CreateBlueprintData $data, User $user): Blueprint
    {
        $this->limitChecker->validateBlueprintLimit($data->organizationId);

        return Blueprint::create([
            'uuid' => $this->uuidGenerator->generate(),
            'organization_id' => $data->organizationId,
            // ...
        ]);
    }
}
```

**Reglas**:
- Una Action = un caso de uso
- Recibe DTOs, no Requests ni arrays crudos
- Retorna Model o DTO, nunca Response
- No depende de HTTP (se puede llamar desde CLI, job, etc.)

### 4.3 DTOs: Transporte Tipado

```php
final readonly class CreateBlueprintData
{
    public function __construct(
        public string $title,
        public ?string $description,
        public int $organizationId,
    ) {}
}
```

**Reglas**:
- `final readonly` (PHP 8.2+)
- Propiedades públicas, tipadas
- Sin lógica (a veces validación básica en constructor)
- Se construyen desde Form Requests o Livewire forms

### 4.4 Value Objects: Validación de Dominio

```php
final readonly class Email
{
    public function __construct(public string $value)
    {
        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('Invalid email');
        }
        $this->value = strtolower($value);
    }
}
```

**Reglas**:
- Inmutables
- Validan en constructor (fail fast)
- Comparables (`equals()` method si aplica)
- Viven en `app/Modules/Shared/ValueObjects/`

### 4.5 Policies: Autorización por Recurso

```php
class BlueprintPolicy
{
    public function view(User $user, Blueprint $blueprint): bool
    {
        return $user->belongsToOrganization($blueprint->organization_id);
    }

    public function update(User $user, Blueprint $blueprint): bool
    {
        if ($user->isOwnerOf($blueprint->organization)) return true;
        if ($user->isMaintainerOf($blueprint->organization)) return true;
        return $user->id === $blueprint->created_by;
    }
}
```

**Reglas**:
- Un Policy por Model
- Métodos nombrados según acción (`view`, `create`, `update`, `delete`)
- Registrados en `AuthServiceProvider::boot()` con `Gate::policy()`
- Se usan en Controllers (`$this->authorize('update', $blueprint)`) o middleware

---

## 5. Patrones Aplicados

### 5.1 Action Pattern
**Problema**: Lógica de negocio dispersa en controllers, difícil de testear y reutilizar.  
**Solución**: Encapsular cada operación de negocio en una clase Action con dependencias inyectadas.  
**Beneficio**: Testable sin HTTP, reusable en CLI/API, controllers delgados.

### 5.2 DTO Pattern
**Problema**: Arrays asociativos sin tipos propagan errores silenciosos.  
**Solución**: Objetos `final readonly` con propiedades tipadas.  
**Beneficio**: Type safety, autocompletion, documentación inline.

### 5.3 Value Object Pattern
**Problema**: Validación de dominio repetida en múltiples lugares.  
**Solución**: Clases inmutables que validan en construcción.  
**Beneficio**: Fail fast, consistencia, inmutabilidad garantizada.

### 5.4 Repository Pattern (parcial)
**Problema**: Queries complejas dispersas.  
**Solución**: Eloquent Scopes y Query Builders en los Models. No hay repositories explícitos porque Eloquent ya abstrae suficiente.  
**Excepción**: Si una query es usada en 3+ lugares, extraer a un Scope o clase Query.

### 5.5 Plugin Architecture (Tabs)
**Problema**: Nuevos tipos de contenido de blueprint sin alterar schema.  
**Solución**: `TabType` enum + `TabManager` genérico. Cada tipo define su config default.  
**Beneficio**: Extensible sin migraciones. Para agregar un nuevo tipo: añadir caso al enum + config en `TabManager::addTab()`.

### AiContext Segments (replaces Presets/Skills)

The AI Context tab uses a segment-based model:

- `AiContextSegment` DTO: `type` (skill|custom|agent enum), `name`, `content`
- `AiContextConfig`: wraps ordered `segments[]` array
- `AgentGenerator::resolveSegments()`: resolves registry content per segment
- `AgentGenerator::generate()`: iterates segments, generates per-segment markdown
- Segments consume variable slots from the plan limit

**Why segments over toggles**: The previous system injected HTML markers into a textarea (fragile regex). Segments are first-class data — typed, ordered, independently editable.

### 5.6 API Token Management (Auth Module)

The Auth module manages API tokens via Laravel Sanctum:

- `HasApiTokens` trait from Sanctum on the `User` model
- `CreateApiToken` and `RevokeApiToken` Actions, both using the `VerifiesPassword` trait for password confirmation
- `ApiTokenManager` Livewire component in the Auth module for UI interaction
- `personal_access_tokens` migration provided by Sanctum

---

## 6. Stack y Dependencias Clave

| Capa | Tecnología | Versión | Propósito |
|------|-----------|---------|-----------|
| Framework | Laravel | 13.x | HTTP, routing, ORM, auth |
| Language | PHP | 8.3+ | Typing, readonly, enums |
| Frontend | Livewire | 3.x | Reactividad sin JS |
| Styling | Tailwind CSS | 3.x+ | Utility-first CSS |
| Build | Vite | — | Asset bundling |
| Auth | Laravel Breeze (custom) | — | Login/register/logout |
| API Ready | Laravel Sanctum | — | API tokens (fase 3) |
| Testing | PHPUnit | 12.5 (487 tests, 1096 assertions) | Unit + Feature tests |
| DB Dev | SQLite | 3 | Desarrollo local |
| DB Prod | MySQL | 8.0+ | Producción |

---

## 7. Decisiones de Arquitectura

### 7.1 ¿Por qué Monolito Modular y no Microservicios?
**Contexto**: MVP, equipo pequeño, cambios frecuentes.  
**Decisión**: Monolito con límites claros (módulos). Cada módulo podría extraerse a microservicio en el futuro sin refactorización masiva.  
**Tradeoff**: Menos overhead operativo ahora. Más trabajo de extracción luego (pero es posible).

### 7.2 ¿Por qué Actions en lugar de Services gigantes?
**Contexto**: Services con 20 métodos se convierten en "god classes".  
**Decisión**: Una clase por caso de uso. 85% de las Actions tienen 1 método público.  
**Tradeoff**: Más archivos, pero cada uno tiene una responsabilidad única y testable.

### 7.3 ¿Por qué Livewire en lugar de API+SPA?
**Contexto**: MVP, velocidad de desarrollo, SEO no crítico (app autenticada).  
**Decisión**: Livewire 3 para reactividad sin construir API REST ni frontend JS.  
**Tradeoff**: Acoplado a Laravel. Pero la lógica de negocio está en Actions, así que reemplazar Livewire por API/SPA no toca el dominio.

### 7.4 ¿Por qué No Repositories Explícitos?
**Contexto**: Eloquent ya abstrae SQL. Queries simples.  
**Decisión**: Usar Eloquent directamente + Scopes para queries reutilizadas.  
**Tradeoff**: Menos capas de abstracción. Si en el futuro se necesita cambiar de Eloquent a Doctrine, habría que refactorizar.

### 7.5 ¿Por qué JSON para Tabs en lugar de Tablas Relacionales?
**Contexto**: Cada tab tiene estructura diferente (extensions[], servers[], segments[]).  
**Decisión**: `tabs_config` JSON en tabla `blueprints` (incluye segments de AI Context).  
**Tradeoff**: No se puede indexar/search fácilmente por contenido de tab. Pero las tabs son siempre accedidas via blueprint (por UUID), así que el lookup es O(1) por índice.

---

**Documento generado**: 2026-06-30  
**Versión**: 1.0  
**Última actualización**: 2026-06-30
