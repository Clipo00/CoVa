# CoVa — Estrategia de Testing

> Pirámide de tests, patrones, fixtures, y guía para escribir tests en CoVa.
> Audiencia: Desarrolladores escribiendo o manteniendo tests.

---

## 1. Pirámide de Tests

```
       /\
      /  \     E2E (Playwright - browser tests)
     /----\
    /      \   Feature Tests (HTTP, flujos completos)
   /--------\ 
  /          \ Unit Tests (Actions, Policies, VO, DTOs)
 /------------\
```

**Distribución actual** (121 tests PHP + 10+ E2E):

| Nivel | Cantidad | % | Ejemplos |
|-------|----------|---|----------|
| **Unit** | ~94 | 78% | Actions, Policies, VO, Services, Tabs |
| **Feature** | ~27 | 22% | Controllers HTTP, Model persistence |
| **E2E** | 10+ | — | Auth, Navigation, Profile (Playwright) |

**Objetivo**: Mantener la proporción ~70% Unit / 30% Feature. E2E se evaluará en Fase 3 (API REST).

---

## 2. Configuración de PHPUnit

Archivo: `phpunit.xml`

```xml
<testsuites>
    <testsuite name="Unit">
        <directory>tests/Unit</directory>
        <directory>app/Modules/*/Tests/Unit</directory>
    </testsuite>
    <testsuite name="Feature">
        <directory>tests/Feature</directory>
        <directory>app/Modules/*/Tests/Feature</directory>
    </testsuite>
</testsuites>
```

**Base de datos de tests**:
- Motor: SQLite in-memory (`:memory:`)
- Configurada en `phpunit.xml` (no tocar `.env`)
- Traits recomendados:
  - `RefreshDatabase`: Reinicia BD entre tests (lento pero seguro)
  - `DatabaseTransactions`: Rollback entre tests (más rápido, pero puede fallar con SQLite + foreign keys)

**Convención**: Usar `RefreshDatabase` por defecto. Si un test suite es muy lento, evaluar `DatabaseTransactions` por suite.

---

## 3. Tests Unitarios

### 3.1 Tests de Actions

**Ubicación**: `app/Modules/{Module}/Tests/Unit/Actions/{ActionName}Test.php`

**Patrón**:
```php
<?php
declare(strict_types=1);

namespace App\Modules\{Module}\Tests\Unit\Actions;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreateEntityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Seeders necesarios para el dominio
        $this->seed(\Database\Seeders\PlanSeeder::class);
    }

    public function test_it_creates_entity_successfully(): void
    {
        // Arrange
        $action = new CreateEntityAction();
        $data = new CreateEntityData(...);

        // Act
        $result = $action->execute($data);

        // Assert
        $this->assertInstanceOf(Entity::class, $result);
        $this->assertDatabaseHas('entities', ['id' => $result->id]);
    }

    public function test_it_throws_exception_when_limit_reached(): void
    {
        // Arrange
        $this->expectException(MaxLimitException::class);
        $action = new CreateEntityAction();

        // Act
        $action->execute($data);
    }
}
```

**Reglas**:
- Testear el happy path y al menos 1 edge case (límites, permisos, excepciones)
- No mockear Eloquent salvo estrictamente necesario (usar BD en memoria es rápido)
- Seeders en `setUp()` para datos base (planes)
- Usar `actingAs()` si la action verifica autenticación

### 3.2 Tests de Policies

**Ubicación**: `app/Modules/{Module}/Tests/Unit/Policies/{Model}PolicyTest.php`

**Patrón**:
```php
class BlueprintPolicyTest extends TestCase
{
    use RefreshDatabase;

    private BlueprintPolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\PlanSeeder::class);
        $this->policy = new BlueprintPolicy();
    }

    public function test_owner_can_update_any_blueprint(): void
    {
        $owner = $this->createOwner();
        $blueprint = $this->createBlueprint($owner);

        $this->assertTrue($this->policy->update($owner, $blueprint));
    }

    public function test_developer_cannot_delete_others_blueprint(): void
    {
        $owner = $this->createOwner();
        $developer = $this->createDeveloperInOrg($owner->organization);
        $blueprint = $this->createBlueprint($owner);

        $this->assertFalse($this->policy->delete($developer, $blueprint));
    }
}
```

**Reglas**:
- Testear TODOS los métodos públicos del Policy
- Testear cada combinación de rol + acción (matriz de permisos)
- Usar helpers privados (`createOwner()`, `createDeveloper()`) para reducir repetición

### 3.3 Tests de Value Objects

**Ubicación**: `app/Modules/Shared/Tests/Unit/ValueObjects/{VoName}Test.php`

**Patrón**:
```php
class EmailTest extends TestCase
{
    public function test_it_accepts_valid_email(): void
    {
        $email = new Email('Test@Example.COM');

        $this->assertEquals('test@example.com', $email->value);
    }

    public function test_it_rejects_invalid_email(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new Email('not-an-email');
    }
}
```

**Reglas**:
- Testear validación en constructor (excepciones)
- Testeer normalización (lowercase, trim, etc.)
- No necesitan `RefreshDatabase` (no tocan BD)

### 3.4 Tests de Services

**Ubicación**: `app/Modules/Shared/Tests/Unit/Services/{ServiceName}Test.php`

**Patrón**:
```php
class PasswordHasherTest extends TestCase
{
    public function test_it_hashes_password(): void
    {
        $hasher = new PasswordHasher();
        $hash = $hasher->hash('secret');

        $this->assertTrue(password_verify('secret', $hash));
    }

    public function test_it_verifies_correct_password(): void
    {
        $hasher = new PasswordHasher();
        $hash = password_hash('secret', PASSWORD_DEFAULT);

        $this->assertTrue($hasher->verify('secret', $hash));
    }
}
```

### 3.5 Tests de Tabs (Plugin Architecture)

**Ubicación**: `app/Modules/Blueprint/Tests/Unit/Tabs/{TabType}TabTest.php`

**Patrón**:
```php
class VscodeExtensionsTabTest extends TestCase
{
    public function test_it_parses_extensions_list(): void
    {
        $tab = new VscodeExtensionsTab();
        $result = $tab->resolve(['extensions' => ['ms-vscode.vscode-typescript', 'bradlc.vscode-tailwindcss']]);

        $this->assertCount(2, $result);
        $this->assertEquals('ms-vscode.vscode-typescript', $result[0]);
    }
}
```

---

## 4. Tests Feature (HTTP)

### 4.1 Tests de Controllers

**Ubicación**: `app/Modules/{Module}/Tests/Feature/{ControllerName}Test.php`

**Patrón**:
```php
class BlueprintControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\PlanSeeder::class);
        // Seed domain-specific data as needed
    }

    public function test_authenticated_user_can_view_blueprint(): void
    {
        $user = $this->createUserWithOrg();
        $blueprint = $this->createBlueprint($user);

        $response = $this->actingAs($user)
            ->get("/blueprints/{$blueprint->uuid}");

        $response->assertOk()
            ->assertViewIs('blueprint::show')
            ->assertViewHas('blueprint');
    }

    public function test_guest_cannot_view_blueprint(): void
    {
        $response = $this->get('/blueprints/some-uuid');

        $response->assertRedirect('/login');
    }

    public function test_user_without_access_gets_403(): void
    {
        $user = $this->createUserWithOrg();
        $otherBlueprint = $this->createBlueprintInOtherOrg();

        $response = $this->actingAs($user)
            ->get("/blueprints/{$otherBlueprint->uuid}");

        $response->assertForbidden();
    }
}
```

**Reglas**:
- Testear autenticación (guest → redirect)
- Testear autorización (sin acceso → 403)
- Testear happy path (200 + view + data)
- Testear validación (422 + errors)
- Testear side effects (BD actualizada, soft delete, etc.)

### 4.2 Tests de Modelos (Feature)

**Ubicación**: `app/Modules/Shared/Tests/Feature/Models/{ModelName}Test.php`

**Patrón**:
```php
class PlanTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_has_correct_limits(): void
    {
        $plan = Plan::create([
            'name' => 'Pro',
            'slug' => 'pro',
            'max_organizations' => 5,
            'max_blueprints_per_org' => 25,
            // ...
        ]);

        $this->assertEquals(5, $plan->max_organizations);
    }
}
```

---

## 5. Fixtures y Helpers

### 5.1 Factories Disponibles

| Factory | Ubicación | Uso |
|---------|-----------|-----|
| `UserFactory` | `database/factories/UserFactory.php` | `User::factory()->create()` |

**Nota**: La mayoría de entidades no tienen factory todavía. Se crean manualmente en tests o via Actions.

### 5.2 Patrón: Creación via Actions en Tests

En lugar de factories, muchos tests crean entidades via Actions (más realista):

```php
private function createUserWithPlan(string $planSlug = 'free'): User
{
    $plan = Plan::where('slug', $planSlug)->first();
    return User::create([
        'name' => 'Test',
        'email' => 'test@example.com',
        'password' => bcrypt('password'),
        'plan_id' => $plan->id,
    ]);
}

private function createOrganization(User $owner): Organization
{
    $action = new CreateOrganization();
    return $action->execute($owner, 'Test Org', 'test-org');
}

private function createBlueprint(Organization $org, string $title = 'Test'): Blueprint
{
    $action = new CreateBlueprint();
    return $action->execute($org, $title, Str::slug($title));
}
```

**Ventaja**: Los tests usan el mismo código de producción para crear datos, detectando bugs en las Actions.

### 5.3 Seeders Base

| Seeder | Datos | Cuándo usar |
|--------|-------|-------------|
| `PlanSeeder` | Free, Pro, Enterprise | Siempre (en `setUp()`) |
| `MarketplaceSeeder` | Org de marketplace | Si el test usa marketplace |

---

## 6. Cobertura

### 6.1 Estado Actual

| Suite | Tests | Assertions |
|-------|-------|------------|
| Auth | 9 | 22 |
| Shared | 34 | 44 |
| Organization | 11 | 30 |
| Blueprint | 7 | 16 |
| Roles/Policies | 14 | 22 |
| **Tab/AI Tests** | ~30+ | ~60+ |
| **Total** | **117** | **219** |

### 6.2 Cobertura por Capa (estimada)

| Capa | Tests | Cobertura estimada |
|------|-------|-------------------|
| Actions | 15+ | ~90% |
| Policies | 4 | ~95% |
| Value Objects | 5 | ~100% |
| Services | 3 | ~100% |
| Tabs/AI | 5 | ~80% |
| Controllers (Feature) | 3 | ~70% |
| Models (Feature) | 2 | ~60% |

### 6.3 Objetivos de Cobertura

| Capa | Actual | Objetivo | Acción |
|------|--------|----------|--------|
| Actions | ~90% | 100% | Añadir tests de edge cases |
| Policies | ~95% | 100% | Completar matriz de permisos |
| VO/Services | ~100% | 100% | Mantener |
| Livewire Forms | ~40% | 80% | Añadir tests de componentes |
| Controllers | ~70% | 85% | Añadir tests de error 422/403 |
| Models (scopes) | ~50% | 70% | Añadir tests de scopes complejos |

---

## 7. Testing de Livewire Components

> Nota: La suite actual tiene poca cobertura de Livewire. Esta sección es guía para el futuro.

### 7.1 Testing Básico

```php
use Livewire\Livewire;

class BlueprintCreateFormTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_blueprint_on_submit(): void
    {
        $user = $this->createUserWithOrg();
        $org = $this->createOrganization($user);

        Livewire::actingAs($user)
            ->test(BlueprintCreateForm::class)
            ->set('title', 'My Blueprint')
            ->set('organization_id', $org->id)
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect('/blueprints/' . Blueprint::first()->uuid);

        $this->assertDatabaseHas('blueprints', ['title' => 'My Blueprint']);
    }

    public function test_it_validates_title_required(): void
    {
        $user = $this->createUserWithOrg();

        Livewire::actingAs($user)
            ->test(BlueprintCreateForm::class)
            ->set('title', '')
            ->call('save')
            ->assertHasErrors(['title' => 'required']);
    }
}
```

### 7.2 Testing de Eventos

```php
public function test_tab_manager_emits_event_on_add(): void
{
    Livewire::test(TabManager::class)
        ->call('addTab', 'vscode_extensions')
        ->assertDispatched('tabs-updated');
}
```

### 7.3 Testing de Componentes Hijos

```php
public function test_parent_receives_tabs_from_child(): void
{
    Livewire::test(BlueprintEditForm::class, ['blueprint' => $blueprint])
        ->call('onTabsUpdated', [['type' => 'ai_context', 'config' => []]])
        ->assertSet('tabsConfig', [['type' => 'ai_context', 'config' => []]]);
}
```

---

## 8. Anti-patrones a Evitar

### ❌ No hacer

```php
// 1. Testear implementación en lugar de comportamiento
$this->assertEquals('App\Modules\Blueprint\Actions\CreateBlueprint', get_class($action));

// 2. Mockear todo (test de implementación, no comportamiento)
$mock = $this->mock(CreateBlueprint::class);
$mock->shouldReceive('execute')->once();

// 3. Tests que dependen de otros tests
// (Cada test debe ser independiente)

// 4. Base de datos compartida entre tests sin RefreshDatabase

// 5. Assertions genéricas sin contexto
$this->assertTrue(true);  // ¿Qué estamos testeando?

// 6. Tests sin Arrange/Act/Assert claros
public function test_something(): void
{
    $x = doThis();
    $y = doThat();
    $z = combine($x, $y);
    assertSomething($z);
    // ¿Cuál es el Act? ¿Cuál es el Assert?
}
```

### ✅ Sí hacer

```php
public function test_creates_blueprint_within_plan_limit(): void
{
    // Arrange
    $user = $this->createUserWithPlan('free');  // 3 blueprints max
    $org = $this->createOrganization($user);
    $action = new CreateBlueprint();
    $action->execute($org, 'BP 1', 'bp-1');
    $action->execute($org, 'BP 2', 'bp-2');

    // Act
    $third = $action->execute($org, 'BP 3', 'bp-3');

    // Assert
    $this->assertInstanceOf(Blueprint::class, $third);
    $this->assertEquals(3, $org->blueprints()->count());
}
```

---

## 9. Tests E2E con Playwright

> Playwright tests viven en `tests/e2e/` y corren contra la aplicación real en un navegador.

### 9.1 Configuración

**Archivo**: `playwright.config.ts`

- Base URL: `http://localhost:8000`
- Navegador: Chromium (headless en CI, headed en dev)
- Auto-start: `php artisan serve --env=testing` antes de los tests
- Screenshots: Solo en fallos
- Traces: Solo en primer retry

### 9.2 Instalación

```bash
# Instalar Playwright
npm install --save-dev @playwright/test

# Instalar browsers
npx playwright install chromium
```

### 9.3 Ejecutar Tests E2E

```bash
# Headless (default)
npm run test:e2e

# Con UI interactiva
npm run test:e2e:ui

# Con navegador visible
npm run test:e2e:headed

# Debug paso a paso
npm run test:e2e:debug

# Un archivo específico
npx playwright test tests/e2e/auth.spec.ts
```

### 9.4 Suite de Tests E2E

| Archivo | Cobertura |
|---------|-----------|
| `tests/e2e/auth.spec.ts` | Login, registro, logout, redirecciones |
| `tests/e2e/navigation.spec.ts` | Dashboard, sidebar, dropdown de usuario, responsive |
| `tests/e2e/profile.spec.ts` | Editar perfil, cambiar contraseña, validaciones |

### 9.5 Patrones de Tests E2E

**Independencia**: Cada test se registra con un email único (timestamp) para evitar conflictos.

**Setup por test**:
```typescript
test.beforeEach(async ({ page }) => {
    // Register fresh user
    await page.goto('/register');
    await page.fill('input#name', 'Test');
    await page.fill('input[type="email"]', `test-${Date.now()}@example.com`);
    await page.fill('input[type="password"]', 'password123');
    await page.fill('input#password_confirmation', 'password123');
    await page.click('button[type="submit"]');
});
```

**Selectores**: Usar `data-testid` para elementos interactivos (dropdowns, botones).

**Aserciones**: Verificar URL + texto visible en página.

### 9.6 CI/CD

En CI, Playwright corre con:
- `workers: 1` (evitar conflictos de BD)
- `retries: 2` (flake tolerance)
- `fullyParallel: false` (secuencial para BD compartida)

---

## 10. Comandos de Testing

```bash
# Suite completa
php artisan test

# Con output detallado
php artisan test --verbose

# Coverage (requiere XDebug o PCOV)
php artisan test --coverage

# Coverage mínimo por archivo
php artisan test --coverage --min=80

# Tests de un módulo
php artisan test app/Modules/Blueprint/Tests

# Tests unitarios solo
php artisan test --testsuite=Unit

# Tests feature solo
php artisan test --testsuite=Feature

# Filtrar por nombre de test
php artisan test --filter=test_it_creates_blueprint

# Paralelo (si PHPUnit soporta)
php artisan test --parallel
```

---

**Documento generado**: 2026-05-15  
**Versión**: 1.1  
**Última actualización**: Tests E2E con Playwright
