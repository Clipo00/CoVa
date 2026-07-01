# Instructions

- Following Playwright test failed.
- Explain why, be concise, respect Playwright best practices.
- Provide a snippet of code with the fix, if possible.

# Test info

- Name: workflows.spec.ts >> Flow 3: Pro user subscribes and votes >> register, org, marketplace → subscribe → vote up
- Location: tests\e2e\workflows.spec.ts:302:5

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
  - text: Charlie Co
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
  44  |     await page.waitForTimeout(500);
  45  | 
  46  |     // --- Step 1: Welcome → click start button (translated text) ---
  47  |     const startBtn = page.getByRole('button', { name: /Empezar|Comenzar|Start|Continuar/i }).first();
  48  |     await expect(startBtn).toBeVisible({ timeout: 5000 });
  49  |     await startBtn.click();
  50  |     await page.waitForTimeout(800);
  51  | 
  52  |     // --- Step 2: Organization (required) ---
  53  |     // Fill org name and wait for Livewire to process the model update
  54  |     await page.fill('input#orgName', orgName);
  55  |     // Wait for Livewire's wire:model.live to sync and validation to clear
  56  |     await page.waitForTimeout(1500);
  57  | 
  58  |     // Click submit — button text is "Crear Organización" (translated)
  59  |     const orgSubmit = page.getByRole('button', { name: /Crear Organización|Create Organization/i });
  60  |     // Wait for button to be enabled (Livewire validation cleared)
  61  |     await expect(orgSubmit).toBeEnabled({ timeout: 8000 });
  62  |     await orgSubmit.click();
  63  |     // Wait for Livewire AJAX response to advance to step 3
  64  |     await page.waitForTimeout(2000);
  65  | 
  66  |     // --- Step 3: Blueprint — skip ---
  67  |     const skipBtn1 = page.getByRole('button', { name: /Omitir|Skip|Saltar/i }).first();
> 68  |     await expect(skipBtn1).toBeVisible({ timeout: 8000 });
      |                            ^ Error: expect(locator).toBeVisible() failed
  69  |     await skipBtn1.click();
  70  |     await page.waitForTimeout(800);
  71  | 
  72  |     // --- Step 4: Invite — skip ---
  73  |     const skipBtn2 = page.getByRole('button', { name: /Omitir|Skip|Saltar/i }).first();
  74  |     await expect(skipBtn2).toBeVisible({ timeout: 8000 });
  75  |     await skipBtn2.click();
  76  |     await page.waitForTimeout(800);
  77  | 
  78  |     // --- Step 5: Done → click complete ---
  79  |     const completeBtn = page.getByRole('button', { name: /Finalizar|Completar|Complete|Ir al Dashboard/i }).first();
  80  |     await expect(completeBtn).toBeVisible({ timeout: 8000 });
  81  |     await completeBtn.click();
  82  |     await page.waitForTimeout(1000);
  83  | 
  84  |     await expect(page).toHaveURL(/dashboard/);
  85  | }
  86  | 
  87  | // ---------------------------------------------------------------------------
  88  | // Flow 1: Owner — full journey
  89  | // ---------------------------------------------------------------------------
  90  | test.describe('Flow 1: Owner creates org, blueprint, Pro trial, publishes', () => {
  91  |     const email = `owner-${timestamp}@cova.test`;
  92  | 
  93  |     test('full owner journey', async ({ page }) => {
  94  |         // 1a. Register
  95  |         await register(page, 'Alice Owner', email);
  96  | 
  97  |         // 1b. Onboarding → creates "Acme Corp"
  98  |         await completeOnboarding(page, 'Acme Corp');
  99  | 
  100 |         // 1c. Go to Blueprints → Create
  101 |         await page.goto('/blueprints');
  102 |         await page.waitForLoadState('networkidle');
  103 | 
  104 |         // Click create link — could be a button or link
  105 |         const createLink = page.locator('a[href*="blueprints/create"]');
  106 |         if (await createLink.isVisible({ timeout: 3000 }).catch(() => false)) {
  107 |             await createLink.click();
  108 |         } else {
  109 |             // Fallback: navigate directly
  110 |             await page.goto('/blueprints/create');
  111 |         }
  112 |         await page.waitForLoadState('networkidle');
  113 | 
  114 |         // 1d. Fill basic info
  115 |         await expect(page.locator('input#title')).toBeVisible({ timeout: 5000 });
  116 |         await page.fill('input#title', 'Laravel API Starter');
  117 | 
  118 |         // Slug auto-generated from title by Livewire — wait then verify
  119 |         await page.waitForTimeout(800);
  120 |         const slugInput = page.locator('input#slug');
  121 |         const slugValue = await slugInput.inputValue();
  122 |         if (!slugValue) {
  123 |             await slugInput.fill('laravel-api-starter');
  124 |         }
  125 | 
  126 |         // 1e. Add tabs via Livewire TabManager component
  127 |         // The tab manager has buttons with wire:click containing the tab type
  128 |         const addTabButtons = page.locator('button[wire\\:click*="addTab"], button[wire\\:click*="tab"]');
  129 |         const tabCount = await addTabButtons.count();
  130 |         // Click available tab-add buttons
  131 |         for (let i = 0; i < Math.min(tabCount, 4); i++) {
  132 |             if (await addTabButtons.nth(i).isVisible({ timeout: 1000 }).catch(() => false)) {
  133 |                 await addTabButtons.nth(i).click();
  134 |                 await page.waitForTimeout(400);
  135 |             }
  136 |         }
  137 | 
  138 |         // 1f. Add variables
  139 |         const addVarBtn = page.locator(
  140 |             'button[wire\\:click*="addVariable"], button[wire\\:click*="var"], ' +
  141 |             'button:has-text("Agregar variable"), button:has-text("Añadir")'
  142 |         ).first();
  143 |         if (await addVarBtn.isVisible({ timeout: 2000 }).catch(() => false)) {
  144 |             await addVarBtn.click();
  145 |             await page.waitForTimeout(400);
  146 | 
  147 |             // Fill first variable fields if visible
  148 |             const keyInput = page.locator('input[wire\\:model*="key"], input[placeholder*="KEY"]').first();
  149 |             if (await keyInput.isVisible({ timeout: 1000 }).catch(() => false)) {
  150 |                 await keyInput.fill('DB_DATABASE');
  151 |             }
  152 |             const valInput = page.locator('input[wire\\:model*="default_value"], input[placeholder*="valor"]').first();
  153 |             if (await valInput.isVisible({ timeout: 500 }).catch(() => false)) {
  154 |                 await valInput.fill('cova_db');
  155 |             }
  156 | 
  157 |             // Add second variable
  158 |             if (await addVarBtn.isVisible({ timeout: 1000 }).catch(() => false)) {
  159 |                 await addVarBtn.click();
  160 |                 await page.waitForTimeout(400);
  161 |                 const keyInput2 = page.locator('input[wire\\:model*="key"], input[placeholder*="KEY"]').last();
  162 |                 if (await keyInput2.isVisible({ timeout: 1000 }).catch(() => false)) {
  163 |                     await keyInput2.fill('APP_DEBUG');
  164 |                 }
  165 |                 const valInput2 = page.locator('input[wire\\:model*="default_value"], input[placeholder*="valor"]').last();
  166 |                 if (await valInput2.isVisible({ timeout: 500 }).catch(() => false)) {
  167 |                     await valInput2.fill('false');
  168 |                 }
```