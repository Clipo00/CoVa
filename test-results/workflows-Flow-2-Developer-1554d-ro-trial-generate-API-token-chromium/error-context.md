# Instructions

- Following Playwright test failed.
- Explain why, be concise, respect Playwright best practices.
- Provide a snippet of code with the fix, if possible.

# Test info

- Name: workflows.spec.ts >> Flow 2: Developer generates API token >> register, skip onboarding, get Pro trial, generate API token
- Location: tests\e2e\workflows.spec.ts:226:5

# Error details

```
Error: expect(locator).toBeVisible() failed

Locator: getByRole('button', { name: /Omitir|Skip|Saltar/i }).first()
Expected: visible
Timeout: 8000ms
Error: element(s) not found

Call log:
  - Expect "toBeVisible" with timeout 8000ms
  - waiting for getByRole('button', { name: /Omitir|Skip|Saltar/i }).first()

```

```yaml
- dialog:
  - iframe
- button "Cambiar tema":
  - img
- button "Idioma":
  - img
  - text: es
  - img
- link "CoVa":
  - /url: /
  - heading "CoVa" [level=1]
- paragraph: Configura tu cuenta
- img
- text: Inicio 2 Organización 3 Blueprint 4 Invitar 5 Listo
- img
- paragraph: Verifica tu correo electrónico para acceder a todas las funcionalidades.
- heading "Crea tu primera organización" [level=2]
- paragraph: Las organizaciones agrupan tus blueprints y permiten colaborar con tu equipo. Este paso es obligatorio para continuar.
- text: Nombre de la organización
- textbox "Nombre de la organización":
  - /placeholder: Mi Empresa
  - text: Dev Org
- button "Crear Organización"
```

# Test source

```ts
  1   | import { test, expect } from '@playwright/test';
  2   | 
  3   | /**
  4   |  * E2E Workflow Tests — CoVa end-to-end user journeys.
  5   |  *
  6   |  * 1. Owner: register → onboarding (org) → blueprint (multi-tab+variables)
  7   |  *    → Pro trial → publish to marketplace
  8   |  * 2. Developer: register → onboarding (skip) → profile/Seguridad → generate API token
  9   |  * 3. Pro user: register → onboarding (org) → marketplace → subscribe → vote up
  10  |  *
  11  |  * Requires: php artisan serve --host=127.0.0.1 --port=8000
  12  |  * Run:      npx playwright test tests/e2e/workflows.spec.ts
  13  |  */
  14  | 
  15  | // ---------------------------------------------------------------------------
  16  | // Helpers
  17  | // ---------------------------------------------------------------------------
  18  | 
  19  | const timestamp = Date.now();
  20  | 
  21  | async function register(
  22  |     page: import('@playwright/test').Page,
  23  |     name: string,
  24  |     email: string,
  25  |     password = 'Password123!',
  26  | ) {
  27  |     await page.goto('/register');
  28  |     await page.fill('input#name', name);
  29  |     await page.fill('input[type="email"]', email);
  30  |     await page.fill('input[type="password"]', password);
  31  |     await page.fill('input#password_confirmation', password);
  32  |     await page.click('button[type="submit"]');
  33  |     await page.waitForLoadState('networkidle');
  34  | }
  35  | 
  36  | /** Navigate onboarding wizard: welcome → org → skip blueprint → skip invite → done. */
  37  | async function completeOnboarding(page: import('@playwright/test').Page, orgName: string) {
  38  |     // Should land on onboarding after registration
  39  |     await expect(page).toHaveURL(/onboarding/);
  40  |     await page.waitForLoadState('networkidle');
  41  | 
  42  |     // --- Step 1: Welcome → click start button (translated text) ---
  43  |     const startBtn = page.getByRole('button', { name: /Empezar|Comenzar|Start|Continuar/i }).first();
  44  |     await expect(startBtn).toBeVisible({ timeout: 5000 });
  45  |     await startBtn.click();
  46  |     await page.waitForTimeout(600);
  47  | 
  48  |     // --- Step 2: Organization (required) ---
  49  |     // Fill org name and wait for Livewire to process the model update
  50  |     await page.fill('input#orgName', orgName);
  51  |     await page.waitForTimeout(800); // Let Livewire wire:model.live sync
  52  | 
  53  |     // Click submit — button text is "Crear Organización" (translated)
  54  |     const orgSubmit = page.getByRole('button', { name: /Crear|Create|Guardar/i }).first();
  55  |     // Wait for button to be enabled (Livewire validation cleared)
  56  |     await expect(orgSubmit).toBeEnabled({ timeout: 5000 });
  57  |     await orgSubmit.click();
  58  |     await page.waitForTimeout(1000);
  59  | 
  60  |     // --- Step 3: Blueprint — skip ---
  61  |     const skipBtn1 = page.getByRole('button', { name: /Omitir|Skip|Saltar/i }).first();
> 62  |     await expect(skipBtn1).toBeVisible({ timeout: 8000 });
      |                            ^ Error: expect(locator).toBeVisible() failed
  63  |     await skipBtn1.click();
  64  |     await page.waitForTimeout(800);
  65  | 
  66  |     // --- Step 4: Invite — skip ---
  67  |     const skipBtn2 = page.getByRole('button', { name: /Omitir|Skip|Saltar/i }).first();
  68  |     await expect(skipBtn2).toBeVisible({ timeout: 8000 });
  69  |     await skipBtn2.click();
  70  |     await page.waitForTimeout(800);
  71  | 
  72  |     // --- Step 5: Done → click complete ---
  73  |     const completeBtn = page.getByRole('button', { name: /Finalizar|Completar|Complete|Ir al Dashboard/i }).first();
  74  |     await expect(completeBtn).toBeVisible({ timeout: 8000 });
  75  |     await completeBtn.click();
  76  |     await page.waitForTimeout(1000);
  77  | 
  78  |     await expect(page).toHaveURL(/dashboard/);
  79  | }
  80  | 
  81  | // ---------------------------------------------------------------------------
  82  | // Flow 1: Owner — full journey
  83  | // ---------------------------------------------------------------------------
  84  | test.describe('Flow 1: Owner creates org, blueprint, Pro trial, publishes', () => {
  85  |     const email = `owner-${timestamp}@cova.test`;
  86  | 
  87  |     test('full owner journey', async ({ page }) => {
  88  |         // 1a. Register
  89  |         await register(page, 'Alice Owner', email);
  90  | 
  91  |         // 1b. Onboarding → creates "Acme Corp"
  92  |         await completeOnboarding(page, 'Acme Corp');
  93  | 
  94  |         // 1c. Go to Blueprints → Create
  95  |         await page.goto('/blueprints');
  96  |         await page.waitForLoadState('networkidle');
  97  | 
  98  |         // Click create link — could be a button or link
  99  |         const createLink = page.locator('a[href*="blueprints/create"]');
  100 |         if (await createLink.isVisible({ timeout: 3000 }).catch(() => false)) {
  101 |             await createLink.click();
  102 |         } else {
  103 |             // Fallback: navigate directly
  104 |             await page.goto('/blueprints/create');
  105 |         }
  106 |         await page.waitForLoadState('networkidle');
  107 | 
  108 |         // 1d. Fill basic info
  109 |         await expect(page.locator('input#title')).toBeVisible({ timeout: 5000 });
  110 |         await page.fill('input#title', 'Laravel API Starter');
  111 | 
  112 |         // Slug auto-generated from title by Livewire — wait then verify
  113 |         await page.waitForTimeout(800);
  114 |         const slugInput = page.locator('input#slug');
  115 |         const slugValue = await slugInput.inputValue();
  116 |         if (!slugValue) {
  117 |             await slugInput.fill('laravel-api-starter');
  118 |         }
  119 | 
  120 |         // 1e. Add tabs via Livewire TabManager component
  121 |         // The tab manager has buttons with wire:click containing the tab type
  122 |         const addTabButtons = page.locator('button[wire\\:click*="addTab"], button[wire\\:click*="tab"]');
  123 |         const tabCount = await addTabButtons.count();
  124 |         // Click available tab-add buttons
  125 |         for (let i = 0; i < Math.min(tabCount, 4); i++) {
  126 |             if (await addTabButtons.nth(i).isVisible({ timeout: 1000 }).catch(() => false)) {
  127 |                 await addTabButtons.nth(i).click();
  128 |                 await page.waitForTimeout(400);
  129 |             }
  130 |         }
  131 | 
  132 |         // 1f. Add variables
  133 |         const addVarBtn = page.locator(
  134 |             'button[wire\\:click*="addVariable"], button[wire\\:click*="var"], ' +
  135 |             'button:has-text("Agregar variable"), button:has-text("Añadir")'
  136 |         ).first();
  137 |         if (await addVarBtn.isVisible({ timeout: 2000 }).catch(() => false)) {
  138 |             await addVarBtn.click();
  139 |             await page.waitForTimeout(400);
  140 | 
  141 |             // Fill first variable fields if visible
  142 |             const keyInput = page.locator('input[wire\\:model*="key"], input[placeholder*="KEY"]').first();
  143 |             if (await keyInput.isVisible({ timeout: 1000 }).catch(() => false)) {
  144 |                 await keyInput.fill('DB_DATABASE');
  145 |             }
  146 |             const valInput = page.locator('input[wire\\:model*="default_value"], input[placeholder*="valor"]').first();
  147 |             if (await valInput.isVisible({ timeout: 500 }).catch(() => false)) {
  148 |                 await valInput.fill('cova_db');
  149 |             }
  150 | 
  151 |             // Add second variable
  152 |             if (await addVarBtn.isVisible({ timeout: 1000 }).catch(() => false)) {
  153 |                 await addVarBtn.click();
  154 |                 await page.waitForTimeout(400);
  155 |                 const keyInput2 = page.locator('input[wire\\:model*="key"], input[placeholder*="KEY"]').last();
  156 |                 if (await keyInput2.isVisible({ timeout: 1000 }).catch(() => false)) {
  157 |                     await keyInput2.fill('APP_DEBUG');
  158 |                 }
  159 |                 const valInput2 = page.locator('input[wire\\:model*="default_value"], input[placeholder*="valor"]').last();
  160 |                 if (await valInput2.isVisible({ timeout: 500 }).catch(() => false)) {
  161 |                     await valInput2.fill('false');
  162 |                 }
```