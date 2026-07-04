---
name: covar-laravel-policy
description: >
  Patrones y convenciones para Policies y autorización en CoVaR. Trigger: Cuando se trabaja con archivos en Policies/.
license: Apache-2.0
metadata:
  author: gentleman-programming
  version: "1.0"
---

## When to Use

- Editando o creando archivos en `app/Modules/{Module}/Policies/`
- Implementando reglas de autorización basadas en roles
- Verificando permisos de Owner/Maintainer/Developer

## Critical Patterns

### Matriz de Permisos (Blueprint)

| Acción | Owner | Maintainer | Developer |
|--------|-------|------------|-----------|
| Ver blueprint | ✅ | ✅ | ✅ |
| Editar blueprint | ✅ (cualquiera) | ✅ (cualquiera) | ✅ (solo suyo) |
| Eliminar blueprint | ✅ (cualquiera) | ❌ | ❌ |
| Favorito | ✅ | ✅ | ✅ |

### Estructura de Policy

```php
<?php

declare(strict_types=1);

namespace App\Modules\Blueprint\Policies;

use App\Modules\Auth\Models\User;
use App\Modules\Blueprint\Models\Blueprint;
use App\Modules\Organization\Models\OrganizationUser;
use Illuminate\Auth\Access\HandlesAuthorization;

class BlueprintPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user, Blueprint $blueprint): bool
    {
        // Cualquiera en la org puede ver
        return $user->isMemberOf($blueprint->organization);
    }

    public function update(User $user, Blueprint $blueprint): bool
    {
        $membership = $user->membershipIn($blueprint->organization);
        
        if (!$membership) {
            return false;
        }

        // Owner y Maintainer pueden editar cualquier blueprint
        if (in_array($membership->role, [OrganizationUser::ROLE_OWNER, OrganizationUser::ROLE_MAINTAINER])) {
            return true;
        }

        // Developer solo puede editar sus propios blueprints
        return $membership->role === OrganizationUser::ROLE_DEVELOPER 
            && $blueprint->created_by === $user->id;
    }

    public function delete(User $user, Blueprint $blueprint): bool
    {
        // Solo Owner puede eliminar
        $membership = $user->membershipIn($blueprint->organization);
        
        return $membership && $membership->role === OrganizationUser::ROLE_OWNER;
    }

    public function toggleFavorite(User $user, Blueprint $blueprint): bool
    {
        // Cualquier miembro puede hacer favorito
        return $user->isMemberOf($blueprint->organization);
    }
}
```

### Roles del Sistema

```php
class OrganizationUser
{
    public const ROLE_OWNER = 'owner';
    public const ROLE_MAINTAINER = 'maintainer';
    public const ROLE_DEVELOPER = 'developer';

    public static function roles(): array
    {
        return [
            self::ROLE_OWNER,
            self::ROLE_MAINTAINER,
            self::ROLE_DEVELOPER,
        ];
    }
}
```

### Helper Methods en User Model

```php
// En app/Modules/Auth/Models/User.php

public function isMemberOf(Organization $organization): bool
{
    return $this->organizations()
        ->where('organizations.id', $organization->id)
        ->exists();
}

public function membershipIn(Organization $organization): ?OrganizationUser
{
    return $this->organizationUsers()
        ->where('organization_id', $organization->id)
        ->first();
}

public function isOwnerOf(Organization $organization): bool
{
    return $this->membershipIn($organization)?->role === OrganizationUser::ROLE_OWNER;
}

public function isMaintainerOf(Organization $organization): bool
{
    return in_array(
        $this->membershipIn($organization)?->role,
        [OrganizationUser::ROLE_OWNER, OrganizationUser::ROLE_MAINTAINER]
    );
}
```

## Reglas de oro

1. **Un policy por modelo** - `BlueprintPolicy` para `Blueprint`, `OrganizationPolicy` para `Organization`
2. **Usar helpers del User** - `isMemberOf()`, `membershipIn()`, `isOwnerOf()`
3. **Retornar bool** - Todos los métodos retornan `bool`
4. ** Owner puede TODO** - Siempre chequear owner primero
5. **Verificar membresía primero** - Si `membershipIn()` es null, deny

## Commands

```bash
# Tests de Policies
php artisan test --filter=BlueprintPolicyTest

# Usar en Controller
$this->authorize('update', $blueprint);
```

## Resources

- **Blueprint Policy**: `app/Modules/Blueprint/Policies/BlueprintPolicy.php`
- **Organization Policy**: `app/Modules/Organization/Policies/OrganizationPolicy.php`
- **User Model helpers**: `app/Modules/Auth/Models/User.php`