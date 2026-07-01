# Instructions

- Following Playwright test failed.
- Explain why, be concise, respect Playwright best practices.
- Provide a snippet of code with the fix, if possible.

# Test info

- Name: workflows.spec.ts >> Flow 2: Developer generates API token >> register, skip onboarding, get Pro trial, generate API token
- Location: tests\e2e\workflows.spec.ts:230:5

# Error details

```
Error: expect(locator).toBeVisible() failed

Locator: getByRole('button', { name: /Omitir|Skip|Saltar/i })
Expected: visible
Timeout: 8000ms
Error: element(s) not found

Call log:
  - Expect "toBeVisible" with timeout 8000ms
  - waiting for getByRole('button', { name: /Omitir|Skip|Saltar/i })

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
  42  |     // Wait for Livewire + Alpine.js to fully initialize
  43  |     await page.waitForSelector('[wire\\:id]', { timeout: 5000 });
  44  |     await page.waitForTimeout(1000);
  45  | 
  46  |     // --- Step 1: Welcome → click start button (translated text) ---
  47  |     const startBtn = page.getByRole('button', { name: /Empezar|Comenzar|Start|Continuar/i }).first();
  48  |     await expect(startBtn).toBeVisible({ timeout: 5000 });
  49  |     await startBtn.click();
  50  |     await page.waitForTimeout(1000);
  51  | 
  52  |     // --- Step 2: Organization (required) ---
  53  |     // Fill org name and wait for Livewire to process the model update
  54  |     await page.fill('input#orgName', orgName);
  55  |     // Wait for Livewire's wire:model.live to sync and validation to clear
  56  |     await page.waitForTimeout(1500);
  57  | 
  58  |     // Click submit — button text is "Crear Organización" (translated)
  59  |     const orgSubmit = page.getByRole('button', { name: /Crear Organización|Create Organization/i });
  60  |     await orgSubmit.click();
  61  |     // Wait for Livewire AJAX response to advance to step 3
  62  |     await page.waitForTimeout(2000);
  63  | 
  64  |     // --- Step 3: Blueprint — skip ---
  65  |     const skipBtn1 = page.getByRole('button', { name: /Omitir|Skip|Saltar/i });
> 66  |     await expect(skipBtn1).toBeVisible({ timeout: 8000 });
      |                            ^ Error: expect(locator).toBeVisible() failed
  67  |     await skipBtn1.click();
  68  |     await page.waitForTimeout(800);
  69  | 
  70  |     // --- Step 4: Invite — skip ---
  71  |     const skipBtn2 = page.getByRole('button', { name: /Omitir|Skip|Saltar/i });
  72  |     await expect(skipBtn2).toBeVisible({ timeout: 8000 });
  73  |     await skipBtn2.click();
  74  |     await page.waitForTimeout(800);
  75  | 
  76  |     // --- Step 5: Done → click complete ---
  77  |     const completeBtn = page.getByRole('button', { name: /Finalizar|Completar|Complete|Ir al Dashboard/i });
  78  |     await expect(completeBtn).toBeVisible({ timeout: 8000 });
  79  |     await completeBtn.click();
  80  |     await page.waitForTimeout(1000);
  81  | 
  82  |     await expect(page).toHaveURL(/dashboard/);
  83  | }
  84  | 
  85  | // ---------------------------------------------------------------------------
  86  | // Flow 1: Owner — full journey
  87  | // ---------------------------------------------------------------------------
  88  | test.describe('Flow 1: Owner creates org, blueprint, Pro trial, publishes', () => {
  89  |     const email = `owner-${timestamp}@cova.test`;
  90  | 
  91  |     test('full owner journey', async ({ page }) => {
  92  |         // 1a. Register
  93  |         await register(page, 'Alice Owner', email);
  94  | 
  95  |         // 1b. Onboarding → creates "Acme Corp"
  96  |         await completeOnboarding(page, 'Acme Corp');
  97  | 
  98  |         // 1c. Go to Blueprints → Create
  99  |         await page.goto('/blueprints');
  100 |         await page.waitForLoadState('networkidle');
  101 | 
  102 |         // Click create link — could be a button or link
  103 |         const createLink = page.locator('a[href*="blueprints/create"]');
  104 |         if (await createLink.isVisible({ timeout: 3000 }).catch(() => false)) {
  105 |             await createLink.click();
  106 |         } else {
  107 |             // Fallback: navigate directly
  108 |             await page.goto('/blueprints/create');
  109 |         }
  110 |         await page.waitForLoadState('networkidle');
  111 | 
  112 |         // 1d. Fill basic info
  113 |         await expect(page.locator('input#title')).toBeVisible({ timeout: 5000 });
  114 |         await page.fill('input#title', 'Laravel API Starter');
  115 | 
  116 |         // Slug auto-generated from title by Livewire — wait then verify
  117 |         await page.waitForTimeout(800);
  118 |         const slugInput = page.locator('input#slug');
  119 |         const slugValue = await slugInput.inputValue();
  120 |         if (!slugValue) {
  121 |             await slugInput.fill('laravel-api-starter');
  122 |         }
  123 | 
  124 |         // 1e. Add tabs via Livewire TabManager component
  125 |         // The tab manager has buttons with wire:click containing the tab type
  126 |         const addTabButtons = page.locator('button[wire\\:click*="addTab"], button[wire\\:click*="tab"]');
  127 |         const tabCount = await addTabButtons.count();
  128 |         // Click available tab-add buttons
  129 |         for (let i = 0; i < Math.min(tabCount, 4); i++) {
  130 |             if (await addTabButtons.nth(i).isVisible({ timeout: 1000 }).catch(() => false)) {
  131 |                 await addTabButtons.nth(i).click();
  132 |                 await page.waitForTimeout(400);
  133 |             }
  134 |         }
  135 | 
  136 |         // 1f. Add variables
  137 |         const addVarBtn = page.locator(
  138 |             'button[wire\\:click*="addVariable"], button[wire\\:click*="var"], ' +
  139 |             'button:has-text("Agregar variable"), button:has-text("Añadir")'
  140 |         ).first();
  141 |         if (await addVarBtn.isVisible({ timeout: 2000 }).catch(() => false)) {
  142 |             await addVarBtn.click();
  143 |             await page.waitForTimeout(400);
  144 | 
  145 |             // Fill first variable fields if visible
  146 |             const keyInput = page.locator('input[wire\\:model*="key"], input[placeholder*="KEY"]').first();
  147 |             if (await keyInput.isVisible({ timeout: 1000 }).catch(() => false)) {
  148 |                 await keyInput.fill('DB_DATABASE');
  149 |             }
  150 |             const valInput = page.locator('input[wire\\:model*="default_value"], input[placeholder*="valor"]').first();
  151 |             if (await valInput.isVisible({ timeout: 500 }).catch(() => false)) {
  152 |                 await valInput.fill('cova_db');
  153 |             }
  154 | 
  155 |             // Add second variable
  156 |             if (await addVarBtn.isVisible({ timeout: 1000 }).catch(() => false)) {
  157 |                 await addVarBtn.click();
  158 |                 await page.waitForTimeout(400);
  159 |                 const keyInput2 = page.locator('input[wire\\:model*="key"], input[placeholder*="KEY"]').last();
  160 |                 if (await keyInput2.isVisible({ timeout: 1000 }).catch(() => false)) {
  161 |                     await keyInput2.fill('APP_DEBUG');
  162 |                 }
  163 |                 const valInput2 = page.locator('input[wire\\:model*="default_value"], input[placeholder*="valor"]').last();
  164 |                 if (await valInput2.isVisible({ timeout: 500 }).catch(() => false)) {
  165 |                     await valInput2.fill('false');
  166 |                 }
```