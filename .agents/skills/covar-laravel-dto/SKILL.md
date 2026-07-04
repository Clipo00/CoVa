---
name: covar-laravel-dto
description: >
  Patrones y convenciones para DTOs y Value Objects en CoVaR. Trigger: Cuando se trabaja con archivos en DTOs/ o ValueObjects/.
license: Apache-2.0
metadata:
  author: gentleman-programming
  version: "1.0"
---

## When to Use

- Creando o editando archivos en `app/Modules/{Module}/DTOs/`
- Creando o editando archivos en `app/Modules/Shared/ValueObjects/`
- Validando datos inmutables

## Critical Patterns

### DTO Pattern

```php
<?php

declare(strict_types=1);

namespace App\Modules\Auth\DTOs;

readonly class LoginUserData
{
    public function __construct(
        public string $email,
        public string $password,
        public bool $remember = false,
    ) {}

    // Factory method (opcional pero recomendado)
    public static function fromArray(array $data): self
    {
        return new self(
            email: $data['email'],
            password: $data['password'],
            remember: $data['remember'] ?? false,
        );
    }
}
```

### DTO en Action

```php
// LoginForm.php (Livewire)
$data = new LoginUserData(
    email: $validated['email'],
    password: $validated['password'],
    remember: $validated['remember'] ?? false,
);

$loginUser->execute($data);

// LoginUser.php (Action)
class LoginUser
{
    public function execute(LoginUserData $data): User
    {
        // $data->email, $data->password, $data->remember
    }
}
```

### Value Object Pattern

```php
<?php

declare(strict_types=1);

namespace App\Modules\Shared\ValueObjects;

use InvalidArgumentException;

class Email
{
    public readonly string $value;

    public function __construct(string $email)
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException("Invalid email address: {$email}");
        }

        // Normalización: lowercase automático
        $this->value = strtolower($email);
    }

    public function __toString(): string
    {
        return $this->value;
    }

    public function equals(Email $other): bool
    {
        return $this->value === $other->value;
    }
}
```

### UUID Value Object

```php
<?php

declare(strict_types=1);

namespace App\Modules\Shared\ValueObjects;

use App\Modules\Shared\Services\UuidGenerator;
use InvalidArgumentException;

class Uuid
{
    public readonly string $value;

    private function __construct(string $uuid)
    {
        if (!preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i', $uuid)) {
            throw new InvalidArgumentException("Invalid UUID v4: {$uuid}");
        }
        
        $this->value = $uuid;
    }

    public static function generate(): self
    {
        return new self((new UuidGenerator())->generate());
    }

    public static function fromString(string $uuid): self
    {
        return new self($uuid);
    }

    public function __toString(): string
    {
        return $this->value;
    }

    public function equals(Uuid $other): bool
    {
        return $this->value === $other->value;
    }
}
```

### Slug Value Object

```php
<?php

declare(strict_types=1);

namespace App\Modules\Shared\ValueObjects;

use InvalidArgumentException;

class Slug
{
    public readonly string $value;

    private function __construct(string $slug)
    {
        // Solo lowercase, números y guiones
        if (!preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $slug)) {
            throw new InvalidArgumentException("Invalid slug: {$slug}");
        }
        
        $this->value = $slug;
    }

    public static function fromString(string $string): self
    {
        // Sanitizar: lowercase, reemplazar espacios con guiones
        $slug = strtolower($string);
        $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
        $slug = trim($slug, '-');
        
        return new self($slug);
    }

    public function __toString(): string
    {
        return $this->value;
    }

    public function equals(Slug $other): bool
    {
        return $this->value === $other->value;
    }
}
```

## Reglas de oro

1. **DTOs son readonly** - Usar `readonly class` o propiedad `public readonly`
2. **Value Objects son inmutables** - Una vez creados, no se modifican
3. **Validación en constructor** - Lanzar `InvalidArgumentException` si los datos son inválidos
4. **Normalización** - Email lowercase, Slug sanitized
5. **`__toString()`** - Siempre implementar para usar en strings
6. **`equals()`** - Para comparar objetos, usar equals() no `===`

## Commands

```bash
# Tests de DTOs y VOs
php artisan test --filter=EmailTest
php artisan test --filter=LoginUserDataTest

# Encontrar todos los VOs
glob app/Modules/Shared/ValueObjects/*.php

# Encontrar todos los DTOs
glob app/Modules/*/DTOs/*.php
```

## Resources

- **Email VO**: `app/Modules/Shared/ValueObjects/Email.php`
- **Uuid VO**: `app/Modules/Shared/ValueObjects/Uuid.php`
- **Slug VO**: `app/Modules/Shared/ValueObjects/Slug.php`
- **LoginUserData DTO**: `app/Modules/Auth/DTOs/LoginUserData.php`
- **RegisterUserData DTO**: `app/Modules/Auth/DTOs/RegisterUserData.php`