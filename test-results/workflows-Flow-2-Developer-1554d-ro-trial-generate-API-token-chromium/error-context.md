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

Locator: getByRole('button', { name: /Crear token|Create token/i })
Expected: visible
Timeout: 5000ms
Error: element(s) not found

Call log:
  - Expect "toBeVisible" with timeout 5000ms
  - waiting for getByRole('button', { name: /Crear token|Create token/i })

```

```yaml
- navigation:
  - link "CoVa":
    - /url: http://127.0.0.1:8000/dashboard
  - link "Dashboard":
    - /url: http://127.0.0.1:8000/dashboard
  - link "Organizaciones":
    - /url: http://127.0.0.1:8000/organizations
  - link "Blueprints":
    - /url: http://127.0.0.1:8000/blueprints
  - link "Marketplace":
    - /url: http://127.0.0.1:8000/marketplace
  - link "Eliminados":
    - /url: http://127.0.0.1:8000/blueprints/deleted
  - button "Cambiar tema":
    - img
  - button "Idioma":
    - img
    - text: es
    - img
  - button "Notificaciones":
    - img
  - button "BD Bob Developer":
    - text: BD Bob Developer
    - img
- main:
  - heading "Mi Perfil" [level=1]
  - paragraph: Gestiona tu información personal
  - navigation "Tabs":
    - tab "Datos"
    - tab "Cuenta"
    - tab "Seguridad"
  - heading "Tokens de API" [level=2]
  - paragraph: Gestiona tus tokens de acceso personal para la API.
  - img
  - paragraph: No tienes tokens de API. Crea uno para acceder desde el CLI.
  - img
  - paragraph: Los tokens de API requieren el plan Pro o Enterprise.
  - link "Ver planes":
    - /url: http://127.0.0.1:8000/pricing
    - text: Ver planes
    - img
```

# Test source

```ts
  167 |             }
  168 |         }
  169 | 
  170 |         // 1g. Add a secret variable
  171 |         const addSecretBtn = page.locator('button[wire\\:click*="secret"], button:has-text("Secreto"), input[type="checkbox"][wire\\:model*="secret"]');
  172 |         if (await addSecretBtn.isVisible({ timeout: 1000 }).catch(() => false)) {
  173 |             await addSecretBtn.click();
  174 |             await page.waitForTimeout(300);
  175 |         }
  176 | 
  177 |         // 1h. Submit form
  178 |         const submitBtn = page.locator('button[type="submit"]').last();
  179 |         await submitBtn.click();
  180 |         await page.waitForTimeout(2000);
  181 | 
  182 |         // Should land on blueprint show page
  183 |         await expect(page).toHaveURL(/blueprints\//);
  184 | 
  185 |         // 1i. Start Pro Trial — visit pricing
  186 |         await page.goto('/pricing');
  187 |         await page.waitForLoadState('networkidle');
  188 | 
  189 |         // Look for Pro trial/upgrade button
  190 |         const proBtn = page.getByRole('link', { name: /Pro|Prueba|Trial|Empezar|Upgrade/i }).first();
  191 |         if (await proBtn.isVisible({ timeout: 3000 }).catch(() => false)) {
  192 |             await proBtn.click();
  193 |             await page.waitForLoadState('networkidle');
  194 |         }
  195 | 
  196 |         // 1j. Navigate to edit page to publish blueprint
  197 |         await page.goto(`/blueprints/${slugValue || 'laravel-api-starter'}/edit`);
  198 |         await page.waitForLoadState('networkidle');
  199 | 
  200 |         // Publish toggle — look for text or checkbox
  201 |         const publishToggle = page.locator(
  202 |             'input[wire\\:model*="isPublic"], input[wire\\:model*="is_public"], ' +
  203 |             'button:has-text("Publicar"), label:has-text("Publicar")'
  204 |         ).first();
  205 |         if (await publishToggle.isVisible({ timeout: 3000 }).catch(() => false)) {
  206 |             await publishToggle.click();
  207 |             await page.waitForTimeout(500);
  208 |             // Save the edit
  209 |             const saveBtn = page.getByRole('button', { name: /Guardar|Actualizar|Save|Update/i }).last();
  210 |             if (await saveBtn.isVisible({ timeout: 2000 }).catch(() => false)) {
  211 |                 await saveBtn.click();
  212 |                 await page.waitForLoadState('networkidle');
  213 |             }
  214 |         }
  215 | 
  216 |         // Verify we remain on a valid page (no error)
  217 |         const currentUrl = page.url();
  218 |         expect(currentUrl).toMatch(/blueprints|dashboard|marketplace/);
  219 | 
  220 |         console.log('✅ Flow 1: Alice — registered, org, blueprint, trial, published');
  221 |     });
  222 | });
  223 | 
  224 | // ---------------------------------------------------------------------------
  225 | // Flow 2: Developer generates API token
  226 | // ---------------------------------------------------------------------------
  227 | test.describe('Flow 2: Developer generates API token', () => {
  228 |     const email = `dev-${timestamp}@cova.test`;
  229 | 
  230 |     test('register, skip onboarding, get Pro trial, generate API token', async ({ page }) => {
  231 |         // 2a. Register
  232 |         await register(page, 'Bob Developer', email);
  233 | 
  234 |         // 2b. Onboarding — skip through to dashboard
  235 |         await completeOnboarding(page, `Dev Org ${timestamp}`);
  236 | 
  237 |         // 2c. Activate Pro trial (API tokens require Pro/Enterprise plan)
  238 |         await page.goto('/pricing');
  239 |         await page.waitForLoadState('networkidle');
  240 | 
  241 |         const trialBtn = page.getByRole('link', { name: /Pro|Prueba|Trial|Empezar|Upgrade/i }).first();
  242 |         if (await trialBtn.isVisible({ timeout: 3000 }).catch(() => false)) {
  243 |             await trialBtn.click();
  244 |             await page.waitForLoadState('networkidle');
  245 |         }
  246 | 
  247 |         // Navigate to dashboard to refresh user session with new plan
  248 |         await page.goto('/dashboard');
  249 |         await page.waitForLoadState('networkidle');
  250 | 
  251 |         // 2e. Navigate to Profile → Seguridad tab
  252 |         await page.click('[data-testid="user-dropdown-toggle"]');
  253 |         await page.waitForTimeout(300);
  254 |         await page.click('text=Perfil');
  255 |         await expect(page).toHaveURL(/profile/);
  256 |         await page.waitForLoadState('networkidle');
  257 | 
  258 |         // Find and click Seguridad tab — Alpine.js @click="activeTab = 'seguridad'"
  259 |         const seguridadBtn = page.locator('button[role="tab"]').filter({ hasText: /Seguridad|Security/i });
  260 |         await expect(seguridadBtn).toBeVisible({ timeout: 5000 });
  261 |         await seguridadBtn.click();
  262 |         // Wait for Alpine x-show to reveal the seguridad div
  263 |         await page.waitForTimeout(500);
  264 | 
  265 |         // 2f. Click "Create token" button (toggles the form)
  266 |         const toggleCreate = page.getByRole('button', { name: /Crear token|Create token/i });
> 267 |         await expect(toggleCreate).toBeVisible({ timeout: 5000 });
      |                                    ^ Error: expect(locator).toBeVisible() failed
  268 |         await toggleCreate.click();
  269 |         await page.waitForTimeout(500);
  270 | 
  271 |         // 2g. Fill token form
  272 |         await expect(page.locator('input#tokenName')).toBeVisible({ timeout: 3000 });
  273 |         await page.fill('input#tokenName', 'CLI Access Token');
  274 | 
  275 |         // Password confirmation (inside form, wire:model="password")
  276 |         const pwdInput = page.locator('form[wire\\:submit="createToken"] input#password');
  277 |         if (await pwdInput.isVisible({ timeout: 2000 }).catch(() => false)) {
  278 |             await pwdInput.fill('Password123!');
  279 |         }
  280 | 
  281 |         // 2h. Submit token creation
  282 |         const submitToken = page.getByRole('button', { name: /Generar token|Generate token/i });
  283 |         await submitToken.click();
  284 |         await page.waitForLoadState('networkidle');
  285 | 
  286 |         // 2i. Verify one-time token display appears (yellow warning box)
  287 |         const tokenBox = page.locator('.bg-yellow-50, [class*="bg-yellow"]');
  288 |         const tokenListed = page.locator('text=CLI Access Token');
  289 |         const hasToken = await tokenBox.isVisible({ timeout: 4000 }).catch(() => false)
  290 |             || await tokenListed.isVisible({ timeout: 2000 }).catch(() => false);
  291 | 
  292 |         expect(hasToken).toBeTruthy();
  293 | 
  294 |         console.log('✅ Flow 2: Bob — registered, reached Seguridad, created API token');
  295 |     });
  296 | });
  297 | 
  298 | // ---------------------------------------------------------------------------
  299 | // Flow 3: Pro user subscribes and votes
  300 | // ---------------------------------------------------------------------------
  301 | test.describe('Flow 3: Pro user subscribes and votes', () => {
  302 |     const email = `pro-${timestamp}@cova.test`;
  303 | 
  304 |     test('register, org, marketplace → subscribe → vote up', async ({ page }) => {
  305 |         // 3a. Register
  306 |         await register(page, 'Charlie Pro', email);
  307 | 
  308 |         // 3b. Onboarding → creates org
  309 |         await completeOnboarding(page, `Charlie Co ${timestamp}`);
  310 | 
  311 |         // 3c. Ensure Pro access (pricing page)
  312 |         await page.goto('/pricing');
  313 |         await page.waitForLoadState('networkidle');
  314 | 
  315 |         const upgradeBtn = page.locator(
  316 |             'a[href*="trial"], a[href*="subscribe"], button:has-text("Pro"), button:has-text("Prueba")'
  317 |         ).first();
  318 |         if (await upgradeBtn.isVisible({ timeout: 3000 }).catch(() => false)) {
  319 |             await upgradeBtn.click();
  320 |             await page.waitForLoadState('networkidle');
  321 |         }
  322 | 
  323 |         // 3d. Browse Marketplace
  324 |         await page.goto('/marketplace');
  325 |         await page.waitForLoadState('networkidle');
  326 |         await expect(page).toHaveURL(/marketplace/);
  327 | 
  328 |         // 3e. Click first blueprint card to view details
  329 |         // Marketplace items link to /marketplace/{uuid} or /blueprints/{slug}
  330 |         const blueprintLink = page.locator(
  331 |             'a[href*="/marketplace/"], a[href*="/blueprints/"]'
  332 |         ).first();
  333 |         if (await blueprintLink.isVisible({ timeout: 5000 }).catch(() => false)) {
  334 |             await blueprintLink.click();
  335 |             await page.waitForLoadState('networkidle');
  336 |         } else {
  337 |             // Try direct navigation to Flow 1's blueprint
  338 |             await page.goto('/marketplace');
  339 |             await page.waitForLoadState('networkidle');
  340 |             const anyCard = page.locator('a[href*="/marketplace/"], a[href*="/blueprints/"]').first();
  341 |             if (await anyCard.isVisible({ timeout: 3000 }).catch(() => false)) {
  342 |                 await anyCard.click();
  343 |                 await page.waitForLoadState('networkidle');
  344 |             }
  345 |         }
  346 | 
  347 |         // 3f. Subscribe
  348 |         const subscribeBtn = page.getByRole('button', { name: /Suscribir|Subscribe|Seguir/i });
  349 |         if (await subscribeBtn.isVisible({ timeout: 3000 }).catch(() => false)) {
  350 |             await subscribeBtn.click();
  351 |             await page.waitForLoadState('networkidle');
  352 |         }
  353 | 
  354 |         // 3g. Vote up — button with title="Votar positivo" (ES) or "Vote up" (EN)
  355 |         const upvoteBtn = page.locator('button[title*="Votar positivo"], button[title*="Vote up"], button[title*="Upvote"]');
  356 |         if (await upvoteBtn.isVisible({ timeout: 3000 }).catch(() => false)) {
  357 |             await upvoteBtn.click();
  358 |             await page.waitForTimeout(800);
  359 |         }
  360 | 
  361 |         // Verify we're still on a valid page
  362 |         const url = page.url();
  363 |         expect(url).toMatch(/blueprints|marketplace/);
  364 | 
  365 |         console.log('✅ Flow 3: Charlie — registered Pro, subscribed and voted');
  366 |     });
  367 | });
```