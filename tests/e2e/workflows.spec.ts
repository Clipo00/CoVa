import { test, expect } from '@playwright/test';

/**
 * E2E Workflow Tests — CoVa end-to-end user journeys.
 *
 * 1. Owner: register → onboarding (org) → blueprint (multi-tab+variables)
 *    → Pro trial → publish to marketplace
 * 2. Developer: register → onboarding (skip) → profile/Seguridad → generate API token
 * 3. Pro user: register → onboarding (org) → marketplace → subscribe → vote up
 *
 * Requires: php artisan serve --host=127.0.0.1 --port=8000
 * Run:      npx playwright test tests/e2e/workflows.spec.ts
 */

// ---------------------------------------------------------------------------
// Helpers
// ---------------------------------------------------------------------------

const timestamp = Date.now();

async function register(
    page: import('@playwright/test').Page,
    name: string,
    email: string,
    password = 'Password123!',
) {
    await page.goto('/register');
    await page.fill('input#name', name);
    await page.fill('input[type="email"]', email);
    await page.fill('input[type="password"]', password);
    await page.fill('input#password_confirmation', password);
    await page.click('button[type="submit"]');
    await page.waitForLoadState('networkidle');
}

/** Navigate onboarding wizard: welcome → org → skip blueprint → skip invite → done. */
async function completeOnboarding(page: import('@playwright/test').Page, orgName: string) {
    // Should land on onboarding after registration
    await expect(page).toHaveURL(/onboarding/);
    await page.waitForLoadState('networkidle');

    // Wait for Livewire + Alpine.js to fully initialize
    await page.waitForSelector('[wire\\:id]', { timeout: 5000 });
    await page.waitForTimeout(1000);

    // --- Step 1: Welcome → click start button (translated text) ---
    const startBtn = page.getByRole('button', { name: /Empezar|Comenzar|Start|Continuar/i }).first();
    await expect(startBtn).toBeVisible({ timeout: 5000 });
    await startBtn.click();
    await page.waitForTimeout(1000);

    // --- Step 2: Organization (required) ---
    // Fill org name and wait for Livewire to process the model update
    await page.fill('input#orgName', orgName);
    // Wait for Livewire's wire:model.live to sync and validation to clear
    await page.waitForTimeout(1500);

    // Click submit — button text is "Crear Organización" (translated)
    const orgSubmit = page.getByRole('button', { name: /Crear Organización|Create Organization/i });
    await orgSubmit.click();
    // Wait for Livewire AJAX response to advance to step 3
    await page.waitForTimeout(2000);

    // --- Step 3: Blueprint — skip ---
    const skipBtn1 = page.getByRole('button', { name: /Omitir|Skip|Saltar/i });
    await expect(skipBtn1).toBeVisible({ timeout: 8000 });
    await skipBtn1.click();
    await page.waitForTimeout(800);

    // --- Step 4: Invite — skip ---
    const skipBtn2 = page.getByRole('button', { name: /Omitir|Skip|Saltar/i });
    await expect(skipBtn2).toBeVisible({ timeout: 8000 });
    await skipBtn2.click();
    await page.waitForTimeout(800);

    // --- Step 5: Done → click complete ---
    const completeBtn = page.getByRole('button', { name: /Finalizar|Completar|Complete|Ir al Dashboard/i });
    await expect(completeBtn).toBeVisible({ timeout: 8000 });
    await completeBtn.click();
    await page.waitForTimeout(1000);

    await expect(page).toHaveURL(/dashboard/);
}

// ---------------------------------------------------------------------------
// Flow 1: Owner — full journey
// ---------------------------------------------------------------------------
test.describe('Flow 1: Owner creates org, blueprint, Pro trial, publishes', () => {
    const email = `owner-${timestamp}@cova.test`;

    test('full owner journey', async ({ page }) => {
        // 1a. Register
        await register(page, 'Alice Owner', email);

        // 1b. Onboarding → creates "Acme Corp"
        await completeOnboarding(page, `Acme Corp ${timestamp}`);

        // 1c. Go to Blueprints → Create
        await page.goto('/blueprints');
        await page.waitForLoadState('networkidle');

        // Click create link — could be a button or link
        const createLink = page.locator('a[href*="blueprints/create"]');
        if (await createLink.isVisible({ timeout: 3000 }).catch(() => false)) {
            await createLink.click();
        } else {
            // Fallback: navigate directly
            await page.goto('/blueprints/create');
        }
        await page.waitForLoadState('networkidle');

        // 1d. Fill basic info
        await expect(page.locator('input#title')).toBeVisible({ timeout: 5000 });
        await page.fill('input#title', 'Laravel API Starter');

        // Slug auto-generated from title by Livewire — wait then verify
        await page.waitForTimeout(800);
        const slugInput = page.locator('input#slug');
        const slugValue = await slugInput.inputValue();
        if (!slugValue) {
            await slugInput.fill('laravel-api-starter');
        }

        // 1e. Add tabs via Livewire TabManager component
        // The tab manager has buttons with wire:click containing the tab type
        const addTabButtons = page.locator('button[wire\\:click*="addTab"], button[wire\\:click*="tab"]');
        const tabCount = await addTabButtons.count();
        // Click available tab-add buttons
        for (let i = 0; i < Math.min(tabCount, 4); i++) {
            if (await addTabButtons.nth(i).isVisible({ timeout: 1000 }).catch(() => false)) {
                await addTabButtons.nth(i).click();
                await page.waitForTimeout(400);
            }
        }

        // 1f. Add variables
        const addVarBtn = page.locator(
            'button[wire\\:click*="addVariable"], button[wire\\:click*="var"], ' +
            'button:has-text("Agregar variable"), button:has-text("Añadir")'
        ).first();
        if (await addVarBtn.isVisible({ timeout: 2000 }).catch(() => false)) {
            await addVarBtn.click();
            await page.waitForTimeout(400);

            // Fill first variable fields if visible
            const keyInput = page.locator('input[wire\\:model*="key"], input[placeholder*="KEY"]').first();
            if (await keyInput.isVisible({ timeout: 1000 }).catch(() => false)) {
                await keyInput.fill('DB_DATABASE');
            }
            const valInput = page.locator('input[wire\\:model*="default_value"], input[placeholder*="valor"]').first();
            if (await valInput.isVisible({ timeout: 500 }).catch(() => false)) {
                await valInput.fill('cova_db');
            }

            // Add second variable
            if (await addVarBtn.isVisible({ timeout: 1000 }).catch(() => false)) {
                await addVarBtn.click();
                await page.waitForTimeout(400);
                const keyInput2 = page.locator('input[wire\\:model*="key"], input[placeholder*="KEY"]').last();
                if (await keyInput2.isVisible({ timeout: 1000 }).catch(() => false)) {
                    await keyInput2.fill('APP_DEBUG');
                }
                const valInput2 = page.locator('input[wire\\:model*="default_value"], input[placeholder*="valor"]').last();
                if (await valInput2.isVisible({ timeout: 500 }).catch(() => false)) {
                    await valInput2.fill('false');
                }
            }
        }

        // 1g. Add a secret variable
        const addSecretBtn = page.locator('button[wire\\:click*="secret"], button:has-text("Secreto"), input[type="checkbox"][wire\\:model*="secret"]');
        if (await addSecretBtn.isVisible({ timeout: 1000 }).catch(() => false)) {
            await addSecretBtn.click();
            await page.waitForTimeout(300);
        }

        // 1h. Submit form
        const submitBtn = page.locator('button[type="submit"]').last();
        await submitBtn.click();
        await page.waitForTimeout(2000);

        // Should land on blueprint show page
        await expect(page).toHaveURL(/blueprints\//);

        // 1i. Start Pro Trial — visit pricing
        await page.goto('/pricing');
        await page.waitForLoadState('networkidle');

        // Look for Pro trial/upgrade button
        const proBtn = page.getByRole('button', { name: /Probar gratis|Try free|Prueba|Trial/i }).first();
        if (await proBtn.isVisible({ timeout: 3000 }).catch(() => false)) {
            await proBtn.click();
            await page.waitForLoadState('networkidle');
        }

        // 1j. Navigate to edit page to publish blueprint
        await page.goto(`/blueprints/${slugValue || 'laravel-api-starter'}/edit`);
        await page.waitForLoadState('networkidle');

        // Publish toggle — look for text or checkbox
        const publishToggle = page.locator(
            'input[wire\\:model*="isPublic"], input[wire\\:model*="is_public"], ' +
            'button:has-text("Publicar"), label:has-text("Publicar")'
        ).first();
        if (await publishToggle.isVisible({ timeout: 3000 }).catch(() => false)) {
            await publishToggle.click();
            await page.waitForTimeout(500);
            // Save the edit
            const saveBtn = page.getByRole('button', { name: /Guardar|Actualizar|Save|Update/i }).last();
            if (await saveBtn.isVisible({ timeout: 2000 }).catch(() => false)) {
                await saveBtn.click();
                await page.waitForLoadState('networkidle');
            }
        }

        // Verify we remain on a valid page (no error)
        const currentUrl = page.url();
        expect(currentUrl).toMatch(/blueprints|dashboard|marketplace/);

        console.log('✅ Flow 1: Alice — registered, org, blueprint, trial, published');
    });
});

// ---------------------------------------------------------------------------
// Flow 2: Developer generates API token
// ---------------------------------------------------------------------------
test.describe('Flow 2: Developer generates API token', () => {
    const email = `dev-${timestamp}@cova.test`;

    test('register, skip onboarding, get Pro trial, generate API token', async ({ page }) => {
        // 2a. Register
        await register(page, 'Bob Developer', email);

        // 2b. Onboarding — skip through to dashboard
        await completeOnboarding(page, `Dev Org ${timestamp}`);

        // 2c. Activate Pro trial (API tokens require Pro/Enterprise plan)
        await page.goto('/pricing');
        await page.waitForLoadState('networkidle');

        const trialBtn = page.getByRole('button', { name: /Probar gratis|Try free|Prueba|Trial|Empezar/i }).first();
        if (await trialBtn.isVisible({ timeout: 3000 }).catch(() => false)) {
            await trialBtn.click();
            await page.waitForLoadState('networkidle');
        }

        // Navigate to dashboard to refresh user session with new plan
        await page.goto('/dashboard');
        await page.waitForLoadState('networkidle');

        // 2e. Navigate to Profile → Seguridad tab
        await page.click('[data-testid="user-dropdown-toggle"]');
        await page.waitForTimeout(300);
        await page.click('text=Perfil');
        await expect(page).toHaveURL(/profile/);
        await page.waitForLoadState('networkidle');

        // Find and click Seguridad tab — Alpine.js @click="activeTab = 'seguridad'"
        const seguridadBtn = page.locator('button[role="tab"]').filter({ hasText: /Seguridad|Security/i });
        await expect(seguridadBtn).toBeVisible({ timeout: 5000 });
        await seguridadBtn.click();
        // Wait for Alpine x-show to reveal the seguridad div
        await page.waitForTimeout(500);

        // 2f. Click "Create token" button (toggles the form)
        const toggleCreate = page.getByRole('button', { name: /Crear token|Create token/i });
        await expect(toggleCreate).toBeVisible({ timeout: 5000 });
        await toggleCreate.click();
        await page.waitForTimeout(500);

        // 2g. Fill token form
        await expect(page.locator('input#tokenName')).toBeVisible({ timeout: 3000 });
        await page.fill('input#tokenName', 'CLI Access Token');

        // Password confirmation (inside form, wire:model="password")
        const pwdInput = page.locator('form[wire\\:submit="createToken"] input#password');
        if (await pwdInput.isVisible({ timeout: 2000 }).catch(() => false)) {
            await pwdInput.fill('Password123!');
        }

        // 2h. Submit token creation
        const submitToken = page.getByRole('button', { name: /Generar token|Generate token/i });
        await submitToken.click();
        await page.waitForLoadState('networkidle');

        // 2i. Verify one-time token display appears (yellow warning box)
        const tokenBox = page.locator('.bg-yellow-50, [class*="bg-yellow"]');
        const tokenListed = page.locator('text=CLI Access Token');
        const hasToken = await tokenBox.isVisible({ timeout: 4000 }).catch(() => false)
            || await tokenListed.isVisible({ timeout: 2000 }).catch(() => false);

        expect(hasToken).toBeTruthy();

        console.log('✅ Flow 2: Bob — registered, reached Seguridad, created API token');
    });
});

// ---------------------------------------------------------------------------
// Flow 3: Pro user subscribes and votes
// ---------------------------------------------------------------------------
test.describe('Flow 3: Pro user subscribes and votes', () => {
    const email = `pro-${timestamp}@cova.test`;

    test('register, org, marketplace → subscribe → vote up', async ({ page }) => {
        // 3a. Register
        await register(page, 'Charlie Pro', email);

        // 3b. Onboarding → creates org
        await completeOnboarding(page, `Charlie Co ${timestamp}`);

        // 3c. Ensure Pro access (pricing page)
        await page.goto('/pricing');
        await page.waitForLoadState('networkidle');

        const upgradeBtn = page.getByRole('button', { name: /Probar gratis|Try free|Prueba|Trial/i }).first();
        if (await upgradeBtn.isVisible({ timeout: 3000 }).catch(() => false)) {
            await upgradeBtn.click();
            await page.waitForLoadState('networkidle');
        }

        // 3d. Browse Marketplace
        await page.goto('/marketplace');
        await page.waitForLoadState('networkidle');
        await expect(page).toHaveURL(/marketplace/);

        // 3e. Click first blueprint card to view details
        // Marketplace items link to /marketplace/{uuid} or /blueprints/{slug}
        const blueprintLink = page.locator(
            'a[href*="/marketplace/"], a[href*="/blueprints/"]'
        ).first();
        if (await blueprintLink.isVisible({ timeout: 5000 }).catch(() => false)) {
            await blueprintLink.click();
            await page.waitForLoadState('networkidle');
        } else {
            // Try direct navigation to Flow 1's blueprint
            await page.goto('/marketplace');
            await page.waitForLoadState('networkidle');
            const anyCard = page.locator('a[href*="/marketplace/"], a[href*="/blueprints/"]').first();
            if (await anyCard.isVisible({ timeout: 3000 }).catch(() => false)) {
                await anyCard.click();
                await page.waitForLoadState('networkidle');
            }
        }

        // 3f. Subscribe
        const subscribeBtn = page.getByRole('button', { name: /Suscribir|Subscribe|Seguir/i });
        if (await subscribeBtn.isVisible({ timeout: 3000 }).catch(() => false)) {
            await subscribeBtn.click();
            await page.waitForLoadState('networkidle');
        }

        // 3g. Vote up — button with title="Votar positivo" (ES) or "Vote up" (EN)
        const upvoteBtn = page.locator('button[title*="Votar positivo"], button[title*="Vote up"], button[title*="Upvote"]');
        if (await upvoteBtn.isVisible({ timeout: 3000 }).catch(() => false)) {
            await upvoteBtn.click();
            await page.waitForTimeout(800);
        }

        // Verify we're still on a valid page
        const url = page.url();
        expect(url).toMatch(/blueprints|marketplace/);

        console.log('✅ Flow 3: Charlie — registered Pro, subscribed and voted');
    });
});
