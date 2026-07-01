# Instructions

- Following Playwright test failed.
- Explain why, be concise, respect Playwright best practices.
- Provide a snippet of code with the fix, if possible.

# Test info

- Name: workflows.spec.ts >> Flow 2: Developer generates API token >> register, skip onboarding, get Pro trial, generate API token
- Location: tests\e2e\workflows.spec.ts:230:5

# Error details

```
Error: expect(received).toBeTruthy()

Received: false
```

# Page snapshot

```yaml
- generic [ref=e2]:
  - navigation [ref=e3]:
    - generic [ref=e5]:
      - generic [ref=e6]:
        - link "CoVa" [ref=e7] [cursor=pointer]:
          - /url: http://127.0.0.1:8000/dashboard
        - generic [ref=e8]:
          - link "Dashboard" [ref=e9] [cursor=pointer]:
            - /url: http://127.0.0.1:8000/dashboard
          - link "Organizaciones" [ref=e10] [cursor=pointer]:
            - /url: http://127.0.0.1:8000/organizations
          - link "Blueprints" [ref=e11] [cursor=pointer]:
            - /url: http://127.0.0.1:8000/blueprints
          - link "Marketplace" [ref=e12] [cursor=pointer]:
            - /url: http://127.0.0.1:8000/marketplace
          - link "Eliminados" [ref=e13] [cursor=pointer]:
            - /url: http://127.0.0.1:8000/blueprints/deleted
      - generic [ref=e14]:
        - button "Cambiar tema" [ref=e16]:
          - img [ref=e17]
        - button "Idioma" [ref=e24]:
          - img [ref=e25]
          - generic [ref=e27]: es
          - img [ref=e28]
        - button "Notificaciones" [ref=e31]:
          - img [ref=e32]
        - button "BD Bob Developer" [ref=e35]:
          - generic [ref=e36]: BD
          - generic [ref=e37]: Bob Developer
          - img [ref=e38]
  - main [ref=e40]:
    - generic [ref=e41]:
      - generic [ref=e42]:
        - heading "Mi Perfil" [level=1] [ref=e43]
        - paragraph [ref=e44]: Gestiona tu información personal
      - generic [ref=e45]:
        - navigation "Tabs" [ref=e47]:
          - tab "Datos" [ref=e48]
          - tab "Cuenta" [ref=e49]
          - tab "Seguridad" [ref=e50]
        - generic [ref=e53]:
          - heading "Tokens de API" [level=2] [ref=e54]
          - paragraph [ref=e55]: Gestiona tus tokens de acceso personal para la API.
          - generic [ref=e56]:
            - generic [ref=e57]:
              - img [ref=e58]
              - paragraph [ref=e60]: No tienes tokens de API. Crea uno para acceder desde el CLI.
            - button "Crear token" [ref=e62]:
              - img [ref=e63]
              - text: Crear token
            - generic [ref=e66]:
              - generic [ref=e67]:
                - generic [ref=e68]: Nombre del token
                - textbox "Nombre del token" [ref=e69]: CLI Access Token
              - generic [ref=e70]:
                - generic [ref=e71]: Fecha de expiración
                - textbox "Fecha de expiración" [ref=e72]
              - generic [ref=e73]:
                - generic [ref=e74]: Contraseña actual
                - textbox "Contraseña actual" [ref=e75]: Password123!
              - button "Generar token" [disabled] [ref=e77]
```

# Test source

```ts
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
  241 |         const trialBtn = page.getByRole('button', { name: /Probar gratis|Try free|Prueba|Trial|Empezar/i }).first();
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
  267 |         await expect(toggleCreate).toBeVisible({ timeout: 5000 });
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
> 292 |         expect(hasToken).toBeTruthy();
      |                          ^ Error: expect(received).toBeTruthy()
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
  315 |         const upgradeBtn = page.getByRole('button', { name: /Probar gratis|Try free|Prueba|Trial/i }).first();
  316 |         if (await upgradeBtn.isVisible({ timeout: 3000 }).catch(() => false)) {
  317 |             await upgradeBtn.click();
  318 |             await page.waitForLoadState('networkidle');
  319 |         }
  320 | 
  321 |         // 3d. Browse Marketplace
  322 |         await page.goto('/marketplace');
  323 |         await page.waitForLoadState('networkidle');
  324 |         await expect(page).toHaveURL(/marketplace/);
  325 | 
  326 |         // 3e. Click first blueprint card to view details
  327 |         // Marketplace items link to /marketplace/{uuid} or /blueprints/{slug}
  328 |         const blueprintLink = page.locator(
  329 |             'a[href*="/marketplace/"], a[href*="/blueprints/"]'
  330 |         ).first();
  331 |         if (await blueprintLink.isVisible({ timeout: 5000 }).catch(() => false)) {
  332 |             await blueprintLink.click();
  333 |             await page.waitForLoadState('networkidle');
  334 |         } else {
  335 |             // Try direct navigation to Flow 1's blueprint
  336 |             await page.goto('/marketplace');
  337 |             await page.waitForLoadState('networkidle');
  338 |             const anyCard = page.locator('a[href*="/marketplace/"], a[href*="/blueprints/"]').first();
  339 |             if (await anyCard.isVisible({ timeout: 3000 }).catch(() => false)) {
  340 |                 await anyCard.click();
  341 |                 await page.waitForLoadState('networkidle');
  342 |             }
  343 |         }
  344 | 
  345 |         // 3f. Subscribe
  346 |         const subscribeBtn = page.getByRole('button', { name: /Suscribir|Subscribe|Seguir/i });
  347 |         if (await subscribeBtn.isVisible({ timeout: 3000 }).catch(() => false)) {
  348 |             await subscribeBtn.click();
  349 |             await page.waitForLoadState('networkidle');
  350 |         }
  351 | 
  352 |         // 3g. Vote up — button with title="Votar positivo" (ES) or "Vote up" (EN)
  353 |         const upvoteBtn = page.locator('button[title*="Votar positivo"], button[title*="Vote up"], button[title*="Upvote"]');
  354 |         if (await upvoteBtn.isVisible({ timeout: 3000 }).catch(() => false)) {
  355 |             await upvoteBtn.click();
  356 |             await page.waitForTimeout(800);
  357 |         }
  358 | 
  359 |         // Verify we're still on a valid page
  360 |         const url = page.url();
  361 |         expect(url).toMatch(/blueprints|marketplace/);
  362 | 
  363 |         console.log('✅ Flow 3: Charlie — registered Pro, subscribed and voted');
  364 |     });
  365 | });
  366 | 
```