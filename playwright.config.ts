import { defineConfig, devices } from '@playwright/test';

/**
 * Playwright configuration for CoVa E2E tests.
 *
 * Requires: php artisan serve (started automatically via webServer)
 * Database: Uses SQLite in-memory or file-based (configured via .env.testing)
 */
export default defineConfig({
    testDir: './tests/e2e',
    fullyParallel: false,
    forbidOnly: !!process.env.CI,
    retries: process.env.CI ? 2 : 0,
    workers: 1,
    reporter: 'list',

    use: {
        baseURL: 'http://127.0.0.1:8000',
        trace: 'on-first-retry',
        screenshot: 'only-on-failure',
    },

    projects: [
        {
            name: 'chromium',
            use: { ...devices['Desktop Chrome'] },
        },
    ],

    /*
     * webServer: comentado porque el servidor debe levantarse manualmente.
     * En Windows/PowerShell, el auto-start de Playwright puede fallar.
     *
     * Para correr tests:
     *   1. php artisan serve --host=127.0.0.1 --port=8000
     *   2. npx playwright test
     */
    // webServer: {
    //     command: 'php artisan serve --host=127.0.0.1 --port=8000',
    //     url: 'http://127.0.0.1:8000',
    //     reuseExistingServer: true,
    //     timeout: 120 * 1000,
    // },
});
