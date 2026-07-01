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
  163 |                 const valInput2 = page.locator('input[wire\\:model*="default_value"], input[placeholder*="valor"]').last();
  164 |                 if (await valInput2.isVisible({ timeout: 500 }).catch(() => false)) {
  165 |                     await valInput2.fill('false');
  166 |                 }
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
  247 |         // 2e. Navigate to Profile → Seguridad tab
  248 |         await page.click('[data-testid="user-dropdown-toggle"]');
  249 |         await page.waitForTimeout(300);
  250 |         await page.click('text=Perfil');
  251 |         await expect(page).toHaveURL(/profile/);
  252 |         await page.waitForLoadState('networkidle');
  253 | 
  254 |         // Find and click Seguridad tab — Alpine.js @click="activeTab = 'seguridad'"
  255 |         const seguridadBtn = page.locator('button[role="tab"]').filter({ hasText: /Seguridad|Security/i });
  256 |         await expect(seguridadBtn).toBeVisible({ timeout: 5000 });
  257 |         await seguridadBtn.click();
  258 |         // Wait for Alpine x-show to reveal the seguridad div
  259 |         await page.waitForTimeout(500);
  260 | 
  261 |         // 2f. Click "Create token" button (toggles the form)
  262 |         const toggleCreate = page.getByRole('button', { name: /Crear token|Create token/i });
> 263 |         await expect(toggleCreate).toBeVisible({ timeout: 5000 });
      |                                    ^ Error: expect(locator).toBeVisible() failed
  264 |         await toggleCreate.click();
  265 |         await page.waitForTimeout(500);
  266 | 
  267 |         // 2g. Fill token form
  268 |         await expect(page.locator('input#tokenName')).toBeVisible({ timeout: 3000 });
  269 |         await page.fill('input#tokenName', 'CLI Access Token');
  270 | 
  271 |         // Password confirmation (inside form, wire:model="password")
  272 |         const pwdInput = page.locator('form[wire\\:submit="createToken"] input#password');
  273 |         if (await pwdInput.isVisible({ timeout: 2000 }).catch(() => false)) {
  274 |             await pwdInput.fill('Password123!');
  275 |         }
  276 | 
  277 |         // 2h. Submit token creation
  278 |         const submitToken = page.getByRole('button', { name: /Generar token|Generate token/i });
  279 |         await submitToken.click();
  280 |         await page.waitForLoadState('networkidle');
  281 | 
  282 |         // 2i. Verify one-time token display appears (yellow warning box)
  283 |         const tokenBox = page.locator('.bg-yellow-50, [class*="bg-yellow"]');
  284 |         const tokenListed = page.locator('text=CLI Access Token');
  285 |         const hasToken = await tokenBox.isVisible({ timeout: 4000 }).catch(() => false)
  286 |             || await tokenListed.isVisible({ timeout: 2000 }).catch(() => false);
  287 | 
  288 |         expect(hasToken).toBeTruthy();
  289 | 
  290 |         console.log('✅ Flow 2: Bob — registered, reached Seguridad, created API token');
  291 |     });
  292 | });
  293 | 
  294 | // ---------------------------------------------------------------------------
  295 | // Flow 3: Pro user subscribes and votes
  296 | // ---------------------------------------------------------------------------
  297 | test.describe('Flow 3: Pro user subscribes and votes', () => {
  298 |     const email = `pro-${timestamp}@cova.test`;
  299 | 
  300 |     test('register, org, marketplace → subscribe → vote up', async ({ page }) => {
  301 |         // 3a. Register
  302 |         await register(page, 'Charlie Pro', email);
  303 | 
  304 |         // 3b. Onboarding → creates org
  305 |         await completeOnboarding(page, `Charlie Co ${timestamp}`);
  306 | 
  307 |         // 3c. Ensure Pro access (pricing page)
  308 |         await page.goto('/pricing');
  309 |         await page.waitForLoadState('networkidle');
  310 | 
  311 |         const upgradeBtn = page.locator(
  312 |             'a[href*="trial"], a[href*="subscribe"], button:has-text("Pro"), button:has-text("Prueba")'
  313 |         ).first();
  314 |         if (await upgradeBtn.isVisible({ timeout: 3000 }).catch(() => false)) {
  315 |             await upgradeBtn.click();
  316 |             await page.waitForLoadState('networkidle');
  317 |         }
  318 | 
  319 |         // 3d. Browse Marketplace
  320 |         await page.goto('/marketplace');
  321 |         await page.waitForLoadState('networkidle');
  322 |         await expect(page).toHaveURL(/marketplace/);
  323 | 
  324 |         // 3e. Click first blueprint card to view details
  325 |         // Marketplace items link to /marketplace/{uuid} or /blueprints/{slug}
  326 |         const blueprintLink = page.locator(
  327 |             'a[href*="/marketplace/"], a[href*="/blueprints/"]'
  328 |         ).first();
  329 |         if (await blueprintLink.isVisible({ timeout: 5000 }).catch(() => false)) {
  330 |             await blueprintLink.click();
  331 |             await page.waitForLoadState('networkidle');
  332 |         } else {
  333 |             // Try direct navigation to Flow 1's blueprint
  334 |             await page.goto('/marketplace');
  335 |             await page.waitForLoadState('networkidle');
  336 |             const anyCard = page.locator('a[href*="/marketplace/"], a[href*="/blueprints/"]').first();
  337 |             if (await anyCard.isVisible({ timeout: 3000 }).catch(() => false)) {
  338 |                 await anyCard.click();
  339 |                 await page.waitForLoadState('networkidle');
  340 |             }
  341 |         }
  342 | 
  343 |         // 3f. Subscribe
  344 |         const subscribeBtn = page.getByRole('button', { name: /Suscribir|Subscribe|Seguir/i });
  345 |         if (await subscribeBtn.isVisible({ timeout: 3000 }).catch(() => false)) {
  346 |             await subscribeBtn.click();
  347 |             await page.waitForLoadState('networkidle');
  348 |         }
  349 | 
  350 |         // 3g. Vote up — button with title="Votar positivo" (ES) or "Vote up" (EN)
  351 |         const upvoteBtn = page.locator('button[title*="Votar positivo"], button[title*="Vote up"], button[title*="Upvote"]');
  352 |         if (await upvoteBtn.isVisible({ timeout: 3000 }).catch(() => false)) {
  353 |             await upvoteBtn.click();
  354 |             await page.waitForTimeout(800);
  355 |         }
  356 | 
  357 |         // Verify we're still on a valid page
  358 |         const url = page.url();
  359 |         expect(url).toMatch(/blueprints|marketplace/);
  360 | 
  361 |         console.log('✅ Flow 3: Charlie — registered Pro, subscribed and voted');
  362 |     });
  363 | });
```