---
name: covar-laravel-controller
description: >
  Patrones y convenciones para Controllers y Routes en CoVa. Trigger: Cuando se trabaja con archivos en Controllers/ o Routes/.
license: Apache-2.0
metadata:
  author: gentleman-programming
  version: "1.0"
---

## When to Use

- Editando o creando archivos en `app/Modules/{Module}/Controllers/`
- Editando rutas en `app/Modules/{Module}/Routes/web.php`
- Orquestando Actions desde Controllers

## Critical Patterns

### Controller Básico

```php
<?php

declare(strict_types=1);

namespace App\Modules\Blueprint\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Blueprint\Actions\CreateBlueprint;
use App\Modules\Blueprint\Livewire\Forms\BlueprintCreateForm;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BlueprintController extends Controller
{
    public function index(): View
    {
        return view('blueprint::index');
    }

    public function create(Request $request): View
    {
        $organizationId = $request->query('org');
        
        return view('blueprint::create', [
            'organizationId' => $organizationId,
        ]);
    }

    public function store(Request $request, CreateBlueprint $createBlueprint): RedirectResponse
    {
        $form = new BlueprintCreateForm();
        $form->fill($request->all());
        
        if (!$form->validate()) {
            return redirect()->back()->withErrors($form->getErrors())->withInput();
        }

        $blueprint = $createBlueprint->execute(
            organization: $form->organization,
            title: $form->title,
            slug: $form->slug,
            description: $form->description,
            categoryId: $form->category_id,
            tabsConfig: $form->tabs_config,
            variables: $form->variables,
        );

        return redirect()->route('blueprints.show', $blueprint->uuid);
    }

    public function show(string $uuid): View
    {
        $blueprint = Blueprint::findByUuidOrFail($uuid);
        
        return view('blueprint::show', [
            'blueprint' => $blueprint,
        ]);
    }
}
```

### Routes (web.php)

```php
<?php

declare(strict_types=1);

use App\Modules\Blueprint\Controllers\BlueprintController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {
    Route::get('/blueprints', [BlueprintController::class, 'index'])
        ->name('blueprints.index');
        
    Route::get('/blueprints/create', [BlueprintController::class, 'create'])
        ->name('blueprints.create');
        
    Route::post('/blueprints', [BlueprintController::class, 'store'])
        ->name('blueprints.store');
        
    Route::get('/blueprints/{uuid}', [BlueprintController::class, 'show'])
        ->name('blueprints.show');
        
    Route::get('/blueprints/{uuid}/edit', [BlueprintController::class, 'edit'])
        ->name('blueprints.edit');
        
    Route::put('/blueprints/{uuid}', [BlueprintController::class, 'update'])
        ->name('blueprints.update');
        
    Route::delete('/blueprints/{uuid}', [BlueprintController::class, 'destroy'])
        ->name('blueprints.destroy');
});
```

### Resource Routes (opcional, para APIs)

```php
Route::resource('blueprints', BlueprintController::class)->parameters([
    'blueprints' => 'uuid'
]);
```

## Reglas de oro

1. **Controllers son orquestadores** - Delegan a Actions, no implementan lógica
2. **Usar form objects** - `BlueprintCreateForm` para validar y obter datos
3. **Naming route**: `blueprints.index`, `blueprints.show`, etc.
4. **UUID como route key** - `{uuid}` en vez de `{id}` para blueprints
5. **Redirect con route()** - `redirect()->route('blueprints.show', $blueprint->uuid)`

## Commands

```bash
# Listar rutas
php artisan route:list

# Tests de Controller
php artisan test --filter=BlueprintControllerTest
```

## Resources

- **Blueprint Controller**: `app/Modules/Blueprint/Controllers/BlueprintController.php`
- **Blueprint Routes**: `app/Modules/Blueprint/Routes/web.php`
- **Controller base**: `app/Http/Controllers/Controller.php`