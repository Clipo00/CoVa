---
name: covar-laravel-livewire
description: >
  Patrones y convenciones para Livewire Components y Forms en CoVa. Trigger: Cuando se trabaja con archivos en Livewire/.
license: Apache-2.0
metadata:
  author: gentleman-programming
  version: "1.0"
---

## When to Use

- Editando o creando archivos en `app/Modules/{Module}/Livewire/`
- Componentes Livewire (Forms, Tables, Components)
- Validación de formularios en tiempo real

## Critical Patterns

### Livewire Form Pattern

```php
<?php

declare(strict_types=1);

namespace App\Modules\Auth\Livewire\Forms;

use App\Modules\Auth\Actions\LoginUser;
use App\Modules\Auth\DTOs\LoginUserData;
use App\Modules\Auth\Requests\LoginRequest;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class LoginForm extends Component
{
    public string $email = '';
    public string $password = '';
    public bool $remember = false;

    // Reglas de validación desde el Request
    protected function rules(): array
    {
        return LoginRequest::rules();
    }

    // Validación en tiempo real por propiedad
    public function updated($propertyName): void
    {
        $this->validateOnly($propertyName);
    }

    // Submit usa la Action correspondiente
    public function submit(LoginUser $loginUser): void
    {
        $validated = $this->validate();

        try {
            $data = new LoginUserData(
                email: $validated['email'],
                password: $validated['password'],
                remember: $validated['remember'] ?? false,
            );

            $loginUser->execute($data);

            $this->redirectIntended(route('dashboard'));
        } catch (ValidationException $e) {
            $this->addError('email', $e->getMessage());
        }
    }

    public function render()
    {
        return view('auth::livewire.forms.login-form');
    }
}
```

### Livewire Table Pattern

```php
<?php

declare(strict_types=1);

namespace App\Modules\Blueprint\Livewire\Tables;

use Livewire\Component;
use Livewire\WithPagination;

class BlueprintList extends Component
{
    use WithPagination;

    public Organization $organization;
    public string $search = '';
    public string $sortField = 'title';
    public bool $sortAsc = true;

    // Query builder con filtros
    public function getBlueprintsProperty()
    {
        return $this->organization->blueprints()
            ->when($this->search, fn($q) => $q->where('title', 'like', "%{$this->search}%"))
            ->orderBy($this->sortField, $this->sortAsc ? 'asc' : 'desc')
            ->paginate(12);
    }

    public function render()
    {
        return view('blueprint::livewire.tables.blueprint-list', [
            'blueprints' => $this->blueprints,
        ]);
    }
}
```

### Livewire Component (UI)

```php
<?php

declare(strict_types=1);

namespace App\Modules\Shared\Livewire\Components;

use Livewire\Component;

class CopyToClipboard extends Component
{
    public string $text = '';
    public string $label = 'Copy';

    public function copy(): void
    {
        $this->dispatch('copy-to-clipboard', text: $this->text);
    }

    public function render()
    {
        return view('shared::livewire.components.copy-to-clipboard');
    }
}
```

## Reglas de oro

1. **Forms usan Request classes** - `LoginRequest::rules()`, no hardcodear reglas
2. **validateOnly() en updated()** - Validación en tiempo real
3. **Actions para lógica de negocio** - El Form solo orquesta, no implementa lógica
4. **DTOs para transferir datos** - Crear Data objects, no arrays
5. **Naming**: `LoginForm`, `BlueprintList`, `CopyToClipboard`

## Blade Integration

```blade
{{-- En la vista del formulario --}}
<form wire:submit="submit">
    <input type="email" wire:model="email" />
    @error('email') <span>{{ $message }}</span> @enderror
    
    <button type="submit">Login</button>
</form>
```

## Commands

```bash
# Tests de Livewire
php artisan test --filter=LoginFormTest

# Crear componente Livewire
# 1. Crear clase en app/Modules/{Module}/Livewire/{Type}/{ComponentName}.php
# 2. Crear vista en app/Modules/{Module}/Views/livewire/{type}/{component-name}.blade.php
```

## Resources

- **Ejemplos reales Forms**: `app/Modules/Auth/Livewire/Forms/LoginForm.php`, `app/Modules/Auth/Livewire/Forms/RegisterForm.php`
- **Ejemplos reales Tables**: `app/Modules/Blueprint/Livewire/Tables/BlueprintList.php`
- **Ejemplos reales Components**: `app/Modules/Shared/Livewire/Components/CopyToClipboard.php`