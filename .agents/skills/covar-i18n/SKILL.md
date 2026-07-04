---
name: covar-i18n
description: >
  Reglas de internacionalización (i18n) para CoVaR. Asegura que todo texto de interfaz
  se añada en español de España (castellano) y su traducción al inglés.
  Trigger: Cuando se crea o edita cualquier texto visible por el usuario, archivos
  en lang/, vistas Blade, mensajes de validación, excepciones, o notificaciones.
license: Apache-2.0
metadata:
  author: gentleman-programming
  version: "1.0"
---

## When to Use

- Se añade o modifica cualquier texto visible por el usuario
- Se crean/editan archivos en `lang/es/` o `lang/en/`
- Se escriben mensajes de error, validación, notificaciones o toasts
- Se modifican vistas Blade con texto estático
- Se crean excepciones con mensajes para el usuario
- Se añaden labels, placeholders, hints o tooltips en formularios

## Critical Rules

### 1. NUNCA hardcodear texto en vistas o código

```php
// ❌ PROHIBIDO - Texto hardcodeado en Blade
<button>Crear Blueprint</button>

// ❌ PROHIBIDO - Texto hardcodeado en PHP
throw new \Exception("No tenés permisos");

// ✅ CORRECTO - Usar función de traducción
<button>{{ __('blueprint.create_button') }}</button>

// ✅ CORRECTO - En PHP
throw new \Exception(__('blueprint.no_permission'));
```

### 2. SIEMPRE en ambos idiomas

Todo cambio en traducciones debe hacerse **simultáneamente** en:
- `lang/es/{archivo}.php` — Español de España (castellano)
- `lang/en/{archivo}.php` — Inglés

```php
// ❌ PROHIBIDO - Solo añadir en español
// lang/es/blueprint.php
'new_feature' => 'Nueva funcionalidad',

// ❌ PROHIBIDO - Olvidar la versión en inglés
// lang/en/blueprint.php
// (vacío o desactualizado)

// ✅ CORRECTO - Ambos archivos actualizados
// lang/es/blueprint.php
'new_feature' => 'Nueva funcionalidad',

// lang/en/blueprint.php
'new_feature' => 'New feature',
```

### 3. Español = Castellano de España (NO rioplatense)

El proyecto usa español de España. Diferencias clave respecto al rioplatense:

| Rioplatense (❌) | Castellano (✅) | Inglés |
|-------------------|------------------|--------|
| Creá | Crea | Create |
| Eliminá | Elimina | Delete |
| Probá | Prueba | Try |
| Guardá | Guarda | Save |
| Mirá | Mira | Look |
| Andá | Ve / Ve a | Go |
| Tenés | Tienes | You have |
| Podés | Puedes | You can |
| Querés | Quieres | You want |
| Sabés | Sabes | You know |
| Venís | Vienes | You come |
| Decís | Dices | You say |
| Hacés | Haces | You do |
| Estás seguro de que querés | ¿Estás seguro de que quieres | Are you sure you want |
| No tenés | No tienes | You don't have |
| No podés | No puedes | You can't |

**Reglas gramaticales del castellano:**
- Imperativos: terminan en vocal sin tilde (`Crea`, `Elimina`, `Guarda`)
- Voseo rioplatense (`-ás`, `-és`, `-ís`) → Castellano (`-as`, `-es`, `-is`)
- "Vos" → "Tú" (en interfaces formales) o "Tú" omitido (imperativos)

### 4. Estructura de archivos lang/

```
lang/
├── es/                          # Español de España (castellano)
│   ├── auth.php
│   ├── blueprint.php
│   ├── organization.php
│   ├── dashboard.php
│   ├── layouts.php
│   ├── errors.php
│   ├── shared.php
│   └── welcome.php
└── en/                          # Inglés
    ├── auth.php
    ├── blueprint.php
    ├── organization.php
    ├── dashboard.php
    ├── layouts.php
    ├── errors.php
    ├── shared.php
    └── welcome.php
```

**Convenciones de organización:**
- Un archivo por módulo/dominio (igual que la estructura de módulos)
- Secciones comentadas con `// Nombre de sección`
- Keys en snake_case, descriptivas y con prefijo de contexto si es necesario

### 5. Placeholders y variables

Usar placeholders con `:` para interpolación:

```php
// ✅ CORRECTO
'max_blueprints_reached' => 'Límite de :max blueprints por organización alcanzado en plan :plan.',

// En Blade
<p>{{ __('blueprint.max_blueprints_reached', ['max' => 25, 'plan' => 'Pro']) }}</p>
```

**Reglas de placeholders:**
- Siempre nombrados descriptivamente (`:max`, `:count`, `:name`, `:plan`)
- Nunca usar números (`:1`, `:2`)
- En inglés: mantener placeholders idénticos a los del español

### 6. HTML en traducciones

```php
// ✅ Permitido cuando la traducción necesita formato
'limit_warning' => 'Has alcanzado el límite de <strong>:max blueprints</strong> de tu plan <strong>:plan</strong>.',

// ❌ PROHIBIDO - HTML en la traducción que debería estar en la vista
'button_html' => '<button class="btn">Crear</button>'
```

**Regla**: HTML mínimo y semántico (`<strong>`, `<em>`, `<br>`) OK. Estructuras de UI NO.

### 7. Mensajes de error y validación

```php
// En Request classes — siempre usar __()
public static function rules(): array
{
    return [
        'title' => 'required|string|max:255',
    ];
}

// Mensajes custom de validación
public function messages(): array
{
    return [
        'title.required' => __('blueprint.title_required'),
        'title.max' => __('blueprint.title_max', ['max' => 255]),
    ];
}
```

### 8. Excepciones de dominio

```php
// ✅ CORRECTO
class MaxBlueprintsReachedException extends \RuntimeException
{
    public function __construct(int $maxBlueprints, string $planName)
    {
        parent::__construct(
            __('blueprint.max_blueprints_reached', [
                'max' => $maxBlueprints,
                'plan' => $planName,
            ])
        );
    }
}
```

### 9. Verificación de sincronización

Antes de commitear cualquier cambio en traducciones:

1. Verificar que las keys en `lang/es/` y `lang/en/` coincidan
2. Verificar que no haya keys en un idioma que falten en el otro
3. Verificar que no haya texto hardcodeado en archivos Blade o PHP modificados

```bash
# Verificar que no haya strings hardcodeados en español en vistas
# (buscar texto que no use __() en archivos blade)

# Verificar sincronización de keys entre es/ y en/
# (comparar arrays de archivos correspondientes)
```

## Code Examples

### Ejemplo completo: Añadir un nuevo mensaje

**Escenario**: Se añade un nuevo botón "Duplicar Blueprint".

**Paso 1**: Añadir traducciones

```php
// lang/es/blueprint.php
// Actions
'duplicate_button' => 'Duplicar Blueprint',
'duplicate_success' => 'Blueprint duplicado correctamente.',
```

```php
// lang/en/blueprint.php
// Actions
'duplicate_button' => 'Duplicate Blueprint',
'duplicate_success' => 'Blueprint duplicated successfully.',
```

**Paso 2**: Usar en la vista

```blade
<button>{{ __('blueprint.duplicate_button') }}</button>
```

**Paso 3**: Usar en el controlador/acción

```php
return redirect()
    ->route('blueprints.show', $newBlueprint->uuid)
    ->with('success', __('blueprint.duplicate_success'));
```

### Ejemplo: Placeholders

```php
// lang/es/organization.php
'invite_sent' => 'Invitación enviada a :email para unirse a :organization.',

// lang/en/organization.php
'invite_sent' => 'Invitation sent to :email to join :organization.',
```

```blade
<p>{{ __('organization.invite_sent', ['email' => $email, 'organization' => $org->name]) }}</p>
```

## Checklist i18n

Antes de considerar completa cualquier tarea que involucre texto:

- [ ] ¿Todos los textos nuevos usan `__('module.key')`?
- [ ] ¿Se añadieron las traducciones en `lang/es/`?
- [ ] ¿Se añadieron las traducciones en `lang/en/`?
- [ ] ¿Las keys coinciden exactamente entre ambos idiomas?
- [ ] ¿El español usa castellano de España (no rioplatense)?
- [ ] ¿Los placeholders son descriptivos y consistentes?
- [ ] ¿No hay texto hardcodeado en vistas Blade?
- [ ] ¿No hay texto hardcodeado en PHP (controllers, actions, exceptions)?
- [ ] ¿Se actualizó CHANGELOG.md si es un cambio visible?

## Commands

```bash
# Buscar texto hardcodeado en español en vistas (indicador de falta de traducción)
# Revisar archivos blade modificados manualmente

# Verificar que todos los archivos lang/ tengan su par
ls lang/es/ | sort > /tmp/es_files.txt
ls lang/en/ | sort > /tmp/en_files.txt
diff /tmp/es_files.txt /tmp/en_files.txt
```

## Resources

- **Ejemplos reales**: `lang/es/blueprint.php`, `lang/en/blueprint.php`
- **Configuración**: `config/app.php` — `supported_locales`, `locale`
- **Vistas con traducciones**: `resources/views/layouts/app.blade.php`
- **Excepciones con traducciones**: `app/Modules/Blueprint/Exceptions/MaxBlueprintsReachedException.php`

## Decision Tree

```
¿Se añade/modifica texto visible al usuario?
├── SÍ → ¿Es un mensaje nuevo o edición?
│   ├── Nuevo → Crear key en lang/es/ Y lang/en/
│   ├── Edición → Actualizar AMBOS archivos
│   └── ¿Texto en español rioplatense?
│       ├── SÍ → Convertir a castellano
│       └── NO → Verificar que sea castellano
└── NO → ¿Texto técnico/debug/logs?
    ├── Sí (logs/debug) → Puede ir en inglés directo
    └── No (comentarios, etc.) → Sin restricción
```
