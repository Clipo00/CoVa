import { test, expect } from '@playwright/test';

test.describe('Navigation & Dashboard', () => {
    test.beforeEach(async ({ page }) => {
        // Register and login before each test
        const uniqueEmail = `nav-test-${Date.now()}@example.com`;

        await page.goto('/register');
        await page.fill('input#name', 'Nav Test');
        await page.fill('input[type="email"]', uniqueEmail);
        await page.fill('input[type="password"]', 'password123');
        await page.fill('input#password_confirmation', 'password123');
        await page.click('button[type="submit"]');

        await expect(page).toHaveURL('/dashboard');
    });

    test('dashboard shows welcome message', async ({ page }) => {
        await expect(page.locator('text=Bienvenido')).toBeVisible();
        await expect(page.locator('text=Bienvenido de vuelta, Nav Test')).toBeVisible();
    });

    test('navigation links work', async ({ page }) => {
        // Organizations link
        await page.click('text=Organizaciones');
        await expect(page).toHaveURL('/organizations');

        // Blueprints link
        await page.click('text=Blueprints');
        await expect(page).toHaveURL('/blueprints');

        // Dashboard link
        await page.click('text=Dashboard');
        await expect(page).toHaveURL('/dashboard');
    });

    test('user dropdown shows profile and logout options', async ({ page }) => {
        // Click on user dropdown
        await page.click('[data-testid="user-dropdown-toggle"]');

        // Check for dropdown menu items (within the dropdown panel)
        const dropdown = page.locator('[data-testid="user-dropdown-menu"]').or(
            page.locator('div.absolute.right-0.mt-2') // fallback to class-based selector
        );

        await expect(page.locator('text=Perfil')).toBeVisible();
        await expect(page.locator('text=Cerrar sesión')).toBeVisible();
    });

    test('user can navigate to profile from dropdown', async ({ page }) => {
        await page.click('[data-testid="user-dropdown-toggle"]');
        await page.click('text=Perfil');

        await expect(page).toHaveURL('/profile');
        await expect(page.locator('text=Editar Perfil')).toBeVisible();
    });

    test('mobile menu is hidden on desktop', async ({ page }) => {
        // Desktop viewport is default (1280x720)
        await expect(page.locator('#mobile-menu')).toBeHidden();
    });

    test('mobile menu opens on small viewport', async ({ page }) => {
        await page.setViewportSize({ width: 375, height: 667 });
        await page.goto('/dashboard');

        await page.click('button[aria-label="Abrir menú"]');
        await expect(page.locator('#mobile-menu')).toBeVisible();
        await expect(page.locator('#mobile-menu >> text=Dashboard')).toBeVisible();
    });
});
