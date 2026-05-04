---
name: covar-laravel-action
description: >
  Patrones y convenciones para Actions en CoVa. Trigger: Cuando se trabaja con archivos en Actions/.
license: Apache-2.0
metadata:
  author: gentleman-programming
  version: "1.0"
---

## When to Use

- Editando o creando archivos en `app/Modules/{Module}/Actions/`
- Implementando lógica de negocio (casos de uso)
- Validando límites de planes (MaxBlueprintsReachedException, etc.)

## Critical Patterns

### Estructura de una Action

```php
<?php

declare(strict_types=1);

namespace App\Modules\Blueprint\Actions;

use App\Modules\Blueprint\Exceptions\MaxBlueprintsReachedException;
use App\Modules\Blueprint\Models\Blueprint;
use App\Modules\Organization\Models\Organization;
use App\Modules\Shared\ValueObjects\Uuid;

class CreateBlueprint
{
    public function execute(
        Organization $organization,
        string $title,
        string $slug,
        ?string $description = null,
        ?int $categoryId = null,
        array $tabsConfig = [],
        array $variables = [],
    ): Blueprint {
        // 1. Validar límites del plan
        $plan = $organization->plan;
        $maxBlueprints = $plan->max_blueprints_per_org;

        if ($maxBlueprints !== null && $organization->blueprints()->count() >= $maxBlueprints) {
            throw new MaxBlueprintsReachedException($maxBlueprints, $plan->name);
        }

        // 2. Validar límites de variables
        $maxVariables = $plan->max_variables_per_blueprint;
        $variableCount = count(array_filter($variables, fn($v) => !empty($v['key'])));

        if ($maxVariables !== null && $variableCount > $maxVariables) {
            throw new MaxVariablesReachedException($maxVariables, $plan->name);
        }

        // 3. Crear el modelo
        $blueprint = Blueprint::create([
            'uuid' => (string) Uuid::generate(),
            'organization_id' => $organization->id,
            'category_id' => $categoryId,
            'slug' => $slug,
            'title' => $title,
            'description' => $description,
            'is_public' => false,
            'tabs_config' => $tabsConfig,
            'created_by' => auth()->id(),
        ]);

        // 4. Crear relaciones (variables, etc.)
        foreach ($variables as $variableData) {
            if (empty($variableData['key'])) {
                continue;
            }

            $blueprint->variables()->create([
                'key' => $variableData['key'],
                'type' => $variableData['type'] ?? 'fixed',
                'default_value' => $variableData['default_value'] ?: null,
                'is_interactive' => $variableData['is_interactive'] ?? false,
                'is_secret' => $variableData['is_secret'] ?? false,
                'section' => $variableData['section'] ?? null,
                'sort_order' => 0,
            ]);
        }

        return $blueprint;
    }
}
```

### Reglas de oro

1. **Un método `execute()`** - La acción tiene un único punto de entrada
2. **Inyección de dependencias** - Los servicios se inyectan en el constructor o como parámetros
3. **Validación de límites primero** - Antes de crear, verificar el plan
4. **Lanzar excepciones custom** - `MaxBlueprintsReachedException`, `MaxVariablesReachedException`, `MaxOrganizationsReachedException`
5. **No usar auth() en Actions** - Pasar el usuario como parámetro o inyectar desde el contexto
6. **Retornar el modelo creado** - Para encadenar operaciones

### Excepciones de Límites (encontrar en el codebase)

| Exception | Usada en | Lanzada cuando |
|-----------|----------|----------------|
| `MaxBlueprintsReachedException` | CreateBlueprint | Org alcanza límite de blueprints |
| `MaxVariablesReachedException` | CreateBlueprint, UpdateBlueprint | Variables exceden límite del plan |
| `MaxOrganizationsReachedException` | CreateOrganization | Usuario alcanza límite de orgs |

## Ejemplo de Action con Validación de Plan

```php
public function execute(
    Organization $organization,
    // ... otros parámetros
): Model {
    $plan = $organization->plan;
    
    // Validar límite específico del plan
    $limit = $plan->max_blueprints_per_org; // puede ser null = infinito
    
    if ($limit !== null && $organization->blueprints()->count() >= $limit) {
        throw new MaxBlueprintsReachedException($limit, $plan->name);
    }
    
    // Continuar con la lógica...
}
```

## Commands

```bash
# Tests de Actions
php artisan test --filter=CreateBlueprintTest

# Crear una acción nueva
# 1. Crear archivo en app/Modules/{Module}/Actions/{ActionName}.php
# 2. Seguir el patrón de arriba
# 3. Crear test en app/Modules/{Module}/Tests/Unit/Actions/{ActionName}Test.php
```

## Resources

- **Template**: Ver [assets/action-template.php](assets/action-template.php)
- **Ejemplos reales**: `app/Modules/Blueprint/Actions/CreateBlueprint.php`, `app/Modules/Auth/Actions/LoginUser.php`