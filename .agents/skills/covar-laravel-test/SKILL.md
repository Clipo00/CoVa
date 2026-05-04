---
name: covar-laravel-test
description: >
  Patrones y convenciones para Tests en CoVa. Trigger: Cuando se crea o edita archivos en Tests/.
license: Apache-2.0
metadata:
  author: gentleman-programming
  version: "1.0"
---

## When to Use

- Creando tests en `app/Modules/{Module}/Tests/`
- Escribiendo tests unitarios o feature
- Verificando comportamiento de Actions, Models, Policies

## Critical Patterns

### Test de Action (Unit)

```php
<?php

declare(strict_types=1);

namespace App\Modules\Blueprint\Tests\Unit\Actions;

use App\Modules\Blueprint\Actions\CreateBlueprint;
use App\Modules\Blueprint\Exceptions\MaxBlueprintsReachedException;
use App\Modules\Organization\Models\Organization;
use App\Modules\Shared\Models\Plan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreateBlueprintTest extends TestCase
{
    use RefreshDatabase;

    private CreateBlueprint $action;
    private Organization $organization;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->action = new CreateBlueprint();
        
        // Setup: crear org con plan Free (max 3 blueprints)
        $plan = Plan::where('name', 'Free')->first();
        $this->organization = Organization::factory()->create([
            'plan_id' => $plan->id,
        ]);
    }

    public function test_creates_blueprint_with_valid_data(): void
    {
        $blueprint = $this->action->execute(
            organization: $this->organization,
            title: 'My Blueprint',
            slug: 'my-blueprint',
            description: 'A test blueprint',
        );

        $this->assertNotNull($blueprint->id);
        $this->assertEquals('My Blueprint', $blueprint->title);
        $this->assertNotEmpty($blueprint->uuid);
    }

    public function test_throws_exception_when_max_blueprints_reached(): void
    {
        // Crear 3 blueprints (límite del plan Free)
        $this->organization->blueprints()->createMany(
            array_fill(0, 3, [
                'uuid' => fake()->uuid(),
                'slug' => fake()->slug(),
                'title' => fake()->sentence(),
            ])
        );

        $this->expectException(MaxBlueprintsReachedException::class);
        
        $this->action->execute(
            organization: $this->organization,
            title: 'One More',
            slug: 'one-more',
        );
    }

    public function test_creates_blueprint_with_variables(): void
    {
        $variables = [
            ['key' => 'DB_HOST', 'type' => 'fixed', 'default_value' => 'localhost'],
            ['key' => 'DB_PORT', 'type' => 'empty'],
        ];

        $blueprint = $this->action->execute(
            organization: $this->organization,
            title: 'With Variables',
            slug: 'with-variables',
            variables: $variables,
        );

        $this->assertCount(2, $blueprint->variables);
        $this->assertEquals('DB_HOST', $blueprint->variables->first()->key);
    }
}
```

### Test de Policy (Unit)

```php
<?php

declare(strict_types=1);

namespace App\Modules\Blueprint\Tests\Unit\Policies;

use App\Modules\Auth\Models\User;
use App\Modules\Blueprint\Models\Blueprint;
use App\Modules\Blueprint\Policies\BlueprintPolicy;
use App\Modules\Organization\Models\Organization;
use App\Modules\Organization\Models\OrganizationUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BlueprintPolicyTest extends TestCase
{
    use RefreshDatabase;

    private BlueprintPolicy $policy;
    private Blueprint $blueprint;
    private Organization $organization;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->policy = new BlueprintPolicy();
        $this->organization = Organization::factory()->create();
        $this->blueprint = Blueprint::factory()->create([
            'organization_id' => $this->organization->id,
        ]);
    }

    public function test_owner_can_update_any_blueprint(): void
    {
        $owner = $this->createUserWithRole(OrganizationUser::ROLE_OWNER);
        
        $this->assertTrue($this->policy->update($owner, $this->blueprint));
    }

    public function test_developer_cannot_update_other_users_blueprint(): void
    {
        $developer = $this->createUserWithRole(OrganizationUser::ROLE_DEVELOPER);
        
        // Blueprint creado por otro usuario
        $this->blueprint->update(['created_by' => fake()->randomNumber()]);
        
        $this->assertFalse($this->policy->update($developer, $this->blueprint));
    }

    public function test_developer_can_update_own_blueprint(): void
    {
        $developer = $this->createUserWithRole(OrganizationUser::ROLE_DEVELOPER);
        $this->blueprint->update(['created_by' => $developer->id]);
        
        $this->assertTrue($this->policy->update($developer, $this->blueprint));
    }

    private function createUserWithRole(string $role): User
    {
        $user = User::factory()->create();
        OrganizationUser::factory()->create([
            'user_id' => $user->id,
            'organization_id' => $this->organization->id,
            'role' => $role,
        ]);
        
        return $user;
    }
}
```

### Test Feature (Controller HTTP)

```php
<?php

declare(strict_types=1);

namespace App\Modules\Blueprint\Tests\Feature;

use App\Modules\Auth\Models\User;
use App\Modules\Organization\Models\Organization;
use App\Modules\Organization\Models\OrganizationUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BlueprintControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_shows_blueprints_to_authenticated_user(): void
    {
        $user = User::factory()->create();
        $organization = Organization::factory()->create();
        OrganizationUser::factory()->create([
            'user_id' => $user->id,
            'organization_id' => $organization->id,
        ]);

        $this->actingAs($user)
            ->get(route('blueprints.index'))
            ->assertOk();
    }

    public function test_create_requires_organization(): void
    {
        $user = User::factory()->create();
        
        $this->actingAs($user)
            ->get('/blueprints/create')
            ->assertRedirect(); // Redirect si no hay org
    }
}
```

## Estructura de Tests

```
app/Modules/{Module}/Tests/
├── Unit/
│   ├── Actions/
│   │   └── CreateBlueprintTest.php
│   ├── Models/
│   │   └── BlueprintTest.php
│   ├── Policies/
│   │   └── BlueprintPolicyTest.php
│   └── ValueObjects/
│       └── EmailTest.php
└── Feature/
    ├── Controllers/
    │   └── BlueprintControllerTest.php
    └── Models/
        └── BlueprintTest.php
```

## Commands

```bash
# Todos los tests
php artisan test

# Solo unit tests
php artisan test --testsuite=Unit

# Solo feature tests
php artisan test --testsuite=Feature

# Test específico
php artisan test --filter=CreateBlueprintTest

# Coverage
php artisan test --coverage
```

## Resources

- **Actions tests**: `app/Modules/Blueprint/Tests/Unit/Actions/CreateBlueprintTest.php`
- **Policy tests**: `app/Modules/Blueprint/Tests/Unit/Policies/BlueprintPolicyTest.php`
- **Controller tests**: `app/Modules/Blueprint/Tests/Feature/BlueprintControllerTest.php`