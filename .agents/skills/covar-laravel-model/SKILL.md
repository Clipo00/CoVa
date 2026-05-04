---
name: covar-laravel-model
description: >
  Patrones y convenciones para Models, Migrations y Traits en CoVa. Trigger: Cuando se trabaja con archivos en Models/.
license: Apache-2.0
metadata:
  author: gentleman-programming
  version: "1.0"
---

## When to Use

- Editando o creando archivos en `app/Modules/{Module}/Models/`
- Creando migrations
- Agregando traits como `SoftDeletes`, `HasUuid`

## Critical Patterns

### Model con SoftDeletes y Trait HasUuid

```php
<?php

declare(strict_types=1);

namespace App\Modules\Blueprint\Models;

use App\Modules\Shared\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Blueprint extends Model
{
    use HasUuid, SoftDeletes;

    protected $fillable = [
        'uuid',
        'organization_id',
        'category_id',
        'slug',
        'title',
        'description',
        'is_public',
        'tabs_config',
        'created_by',
    ];

    protected $casts = [
        'is_public' => 'boolean',
        'tabs_config' => 'array',
    ];

    // Relationships
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function variables(): HasMany
    {
        return $this->hasMany(BlueprintVariable::class)->orderBy('sort_order');
    }
}
```

### Trait HasUuid

```php
<?php

declare(strict_types=1);

namespace App\Modules\Shared\Traits;

use App\Modules\Shared\ValueObjects\Uuid;

trait HasUuid
{
    protected static function bootHasUuid(): void
    {
        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Uuid::generate();
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }
}
```

### Relaciones Típicas

```php
// Blueprint -> Organization (belongsTo)
public function organization(): BelongsTo
{
    return $this->belongsTo(Organization::class);
}

// Organization -> Blueprints (hasMany)
public function blueprints(): HasMany
{
    return $this->hasMany(Blueprint::class);
}

// Organization -> Users via organization_user (belongsToMany)
public function users(): BelongsToMany
{
    return $this->belongsToMany(User::class, 'organization_user')
        ->withPivot('role')
        ->withTimestamps();
}
```

## Soft Deletes

CoVa usa soft deletes en:
- **Blueprint** - Para recuperación y mantener favoritos
- **Organization** - Para auditoría

```php
// En la migración
$table->softDeletes();

// En el modelo
use Illuminate\Database\Eloquent\SoftDeletes;

// Queries automáticas excluyen eliminados
// Para incluir: Model::withTrashed()->find($id)
// Para solo eliminados: Model::onlyTrashed()->find($id)
```

## Commands

```bash
# Crear modelo con migración
php artisan make:model -m MyModel

# SoftDeletes en migración existente
php artisan make:migration add_soft_deletes_to_blueprints_table --table=blueprints

# Tests de Model
php artisan test --filter=BlueprintTest
```

## Resources

- **Blueprint Model**: `app/Modules/Blueprint/Models/Blueprint.php`
- **HasUuid Trait**: `app/Modules/Shared/Traits/HasUuid.php`
- **Organization Model**: `app/Modules/Organization/Models/Organization.php`