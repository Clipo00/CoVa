import { test, expect } from '@playwright/test';

test.describe('Authentication', () => {
    test.beforeEach(async ({ page }) => {
        // Clear any existing session
        await page.context().clearCookies();
    });

    test('guest can view login page', async ({ page }) => {
        await page.goto('/login');

        await expect(page).toHaveTitle(/Login/);
        await expect(page.locator('text=Iniciar sesión')).toBeVisible();
        await expect(page.locator('input[type="email"]')).toBeVisible();
        await expect(page.locator('input[type="password"]')).toBeVisible();
    });

    test('guest can view register page', async ({ page }) => {
        await page.goto('/register');

        await expect(page).toHaveTitle(/Registro/);
        await expect(page.locator('text=Crear cuenta')).toBeVisible();
        await expect(page.locator('input#name')).toBeVisible();
        await expect(page.locator('input[type="email"]')).toBeVisible();
    });

    test('user can register and is redirected to dashboard', async ({ page }) => {
        const uniqueEmail = `test-${Date.now()}@example.com`;

        await page.goto('/register');

        await page.fill('input#name', 'Test User');
        await page.fill('input[type="email"]', uniqueEmail);
        await page.fill('input[type="password"]', 'password123');
        await page.fill('input#password_confirmation', 'password123');
        await page.click('button[type="submit"]');

        // Should redirect to dashboard
        await expect(page).toHaveURL('/dashboard');
        await expect(page.locator('text=Bienvenido')).toBeVisible();
    });

    test('user can login with valid credentials', async ({ page }) => {
        // First register a user
        const uniqueEmail = `login-test-${Date.now()}@example.com`;

        await page.goto('/register');
        await page.fill('input#name', 'Login Test');
        await page.fill('input[type="email"]', uniqueEmail);
        await page.fill('input[type="password"]', 'password123');
        await page.fill('input#password_confirmation', 'password123');
        await page.click('button[type="submit"]');

        await expect(page).toHaveURL('/dashboard');

        // Logout - open dropdown first
        await page.click('[data-testid="user-dropdown-toggle"]');
        await page.click('text=Cerrar sesión');
        await expect(page).toHaveURL('/login');

        // Login
        await page.fill('input[type="email"]', uniqueEmail);
        await page.fill('input[type="password"]', 'password123');
        await page.click('button[type="submit"]');

        await expect(page).toHaveURL('/dashboard');
        await expect(page.locator('p', { hasText: 'Bienvenido de vuelta, Login Test' })).toBeVisible();
    });

    test('login shows error with invalid credentials', async ({ page }) => {
        await page.goto('/login');

        await page.fill('input[type="email"]', 'nonexistent@example.com');
        await page.fill('input[type="password"]', 'wrongpassword');
        await page.click('button[type="submit"]');

        await expect(page).toHaveURL('/login');
        await expect(page.locator('text=Las credenciales proporcionadas no son correctas')).toBeVisible();
    });

    test('guest is redirected to login when accessing protected route', async ({ page }) => {
        await page.goto('/dashboard');

        await expect(page).toHaveURL('/login');
    });
});
