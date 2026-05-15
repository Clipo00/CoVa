import { test, expect } from '@playwright/test';

test.describe('User Profile', () => {
    test.beforeEach(async ({ page }) => {
        // Register and login before each test
        const uniqueEmail = `profile-test-${Date.now()}@example.com`;

        await page.goto('/register');
        await page.fill('input#name', 'Profile Test');
        await page.fill('input[type="email"]', uniqueEmail);
        await page.fill('input[type="password"]', 'password123');
        await page.fill('input#password_confirmation', 'password123');
        await page.click('button[type="submit"]');

        await expect(page).toHaveURL('/dashboard');

        // Navigate to profile
        await page.click('[data-testid="user-dropdown-toggle"]');
        await page.click('text=Perfil');
        await expect(page).toHaveURL('/profile');
    });

    test('profile page shows current user data', async ({ page }) => {
        await expect(page.locator('text=Editar Perfil')).toBeVisible();
        await expect(page.locator('input#name')).toHaveValue('Profile Test');
    });

    test('user can update name', async ({ page }) => {
        await page.fill('input#name', 'Updated Name');
        await page.click('[data-testid="profile-submit"]');

        // Wait for the request to complete (Livewire may take a moment)
        await page.waitForLoadState('networkidle');

        // Reload to verify persistence
        await page.reload();
        await expect(page.locator('input#name')).toHaveValue('Updated Name');
    });

    test('user can update email', async ({ page }) => {
        const newEmail = `updated-${Date.now()}@example.com`;

        await page.fill('input#email', newEmail);
        await page.click('[data-testid="profile-submit"]');

        await page.waitForLoadState('networkidle');

        // Reload and verify
        await page.reload();
        await expect(page.locator('input#email')).toHaveValue(newEmail);
    });

    test('user can navigate from profile back to dashboard', async ({ page }) => {
        await page.click('text=Dashboard');
        await expect(page).toHaveURL('/dashboard');
    });

    test('profile form has all required fields', async ({ page }) => {
        await expect(page.locator('input#name')).toBeVisible();
        await expect(page.locator('input#email')).toBeVisible();
        await expect(page.locator('input[type="file"]')).toBeVisible();
        await expect(page.locator('input#currentPassword')).toBeVisible();
        await expect(page.locator('input#newPassword')).toBeVisible();
        await expect(page.locator('input#newPasswordConfirmation')).toBeVisible();
        await expect(page.locator('[data-testid="profile-submit"]')).toBeVisible();
    });
});
