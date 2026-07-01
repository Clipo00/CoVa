import { test, expect } from '@playwright/test';

test('debug onboarding — register and check step 2 state', async ({ page }) => {
    const email = `debug-${Date.now()}@cova.test`;

    // Register
    await page.goto('/register');
    await page.fill('input#name', 'Debug User');
    await page.fill('input[type="email"]', email);
    await page.fill('input[type="password"]', 'Password123!');
    await page.fill('input#password_confirmation', 'Password123!');
    await page.click('button[type="submit"]');
    await page.waitForLoadState('networkidle');

    // Should be on onboarding
    await expect(page).toHaveURL(/onboarding/);
    console.log('URL:', page.url());

    // Wait for Livewire
    await page.waitForSelector('[wire\\:id]', { timeout: 5000 });
    await page.waitForTimeout(1000);

    // Check for dialogs
    const dialogCount = await page.locator('dialog, [role="dialog"], .modal, .fixed.inset-0').count();
    console.log('Dialogs found:', dialogCount);

    // Dismiss any dialogs
    const dismissBtn = page.locator('dialog button, [role="dialog"] button, button:has-text("Cerrar"), button:has-text("Close"), button:has-text("×")').first();
    if (await dismissBtn.isVisible({ timeout: 1000 }).catch(() => false)) {
        console.log('Dismissing dialog');
        await dismissBtn.click();
        await page.waitForTimeout(500);
    }

    // Check current step
    const heading = page.locator('h1, h2').first();
    console.log('Page heading:', await heading.textContent());

    // Click start
    const startBtn = page.getByRole('button', { name: /Empezar|Comenzar|Start|Continuar/i }).first();
    if (await startBtn.isVisible({ timeout: 2000 }).catch(() => false)) {
        console.log('Clicking start button');
        await startBtn.click();
        await page.waitForTimeout(1000);
    }

    // Check heading after click
    const heading2 = page.locator('h2').first();
    console.log('Step 2 heading:', await heading2.textContent());

    // Fill org name
    await page.fill('input#orgName', 'Test Org');
    await page.waitForTimeout(1500);

    // Check button state
    const orgBtn = page.getByRole('button', { name: /Crear Organización|Create Organization/i });
    const isEnabled = await orgBtn.isEnabled();
    console.log('Org button enabled:', isEnabled);

    if (isEnabled) {
        // Click and wait
        console.log('Clicking Crear Organización...');
        await orgBtn.click();
        await page.waitForTimeout(2000);

        // Check what happened
        const heading3 = page.locator('h2').first();
        console.log('After submit heading:', await heading3.textContent());
        console.log('After submit URL:', page.url());

        // Look for skip button
        const skipBtn = page.getByRole('button', { name: /Omitir|Skip|Saltar/i });
        const skipVisible = await skipBtn.isVisible({ timeout: 1000 }).catch(() => false);
        console.log('Skip button visible:', skipVisible);

        // Look for errors
        const errors = page.locator('.text-red-500, .text-red-600, [class*="error"]');
        const errorCount = await errors.count();
        for (let i = 0; i < errorCount; i++) {
            console.log('Error:', await errors.nth(i).textContent());
        }
    }

    // Take screenshot
    await page.screenshot({ path: 'test-results/debug-onboarding.png' });
    console.log('Screenshot saved to test-results/debug-onboarding.png');
});
