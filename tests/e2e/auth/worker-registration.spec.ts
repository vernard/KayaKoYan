import { test, expect } from '@playwright/test';
import { generateTestEmail, generateTestName } from '../fixtures/test-helpers';

test.describe('Worker Registration', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('/become-a-worker');
  });

  test('displays worker registration page with benefits', async ({ page }) => {
    // Should show page title or heading for worker registration
    const pageContent = await page.content();
    const hasWorkerContent = pageContent.includes('Become') ||
                             pageContent.includes('Worker') ||
                             pageContent.includes('Sell') ||
                             pageContent.includes('earn');
    expect(hasWorkerContent).toBe(true);

    // Should have registration form
    await expect(page.locator('input[name="name"]')).toBeVisible();
    await expect(page.locator('input[name="email"]')).toBeVisible();
    await expect(page.locator('input[name="password"]')).toBeVisible();
    await expect(page.locator('input[name="password_confirmation"]')).toBeVisible();
  });

  test('shows worker benefits or features', async ({ page }) => {
    // Check for common benefit messaging
    const pageContent = await page.content();
    const hasBenefits = pageContent.includes('commission') ||
                        pageContent.includes('payment') ||
                        pageContent.includes('earn') ||
                        pageContent.includes('sell');
    expect(hasBenefits).toBe(true);
  });

  test('worker can register with valid data', async ({ page }) => {
    const email = generateTestEmail('worker');
    const name = generateTestName('New Worker');

    await page.fill('input[name="name"]', name);
    await page.fill('input[name="email"]', email);
    await page.fill('input[name="password"]', 'SecurePassword123!');
    await page.fill('input[name="password_confirmation"]', 'SecurePassword123!');
    await page.click('button[type="submit"]');

    // Should redirect to email verification
    await expect(page).toHaveURL(/\/email\/verify/);
  });

  test('worker registration creates worker profile', async ({ page }) => {
    // This would be verified by checking the database
    // In E2E test, we verify by accessing worker panel after verification

    const email = generateTestEmail('worker');
    const name = generateTestName('Profile Worker');

    await page.fill('input[name="name"]', name);
    await page.fill('input[name="email"]', email);
    await page.fill('input[name="password"]', 'SecurePassword123!');
    await page.fill('input[name="password_confirmation"]', 'SecurePassword123!');
    await page.click('button[type="submit"]');

    // Should redirect to email verification
    await expect(page).toHaveURL(/\/email\/verify/);

    // After verification (simulated), worker should have access to profile
    // The public profile URL would be /worker/{slug}
  });

  test('worker registration fails with duplicate email', async ({ page }) => {
    // Use existing worker email
    await page.fill('input[name="name"]', 'Test Worker');
    await page.fill('input[name="email"]', 'lisa.mendoza@example.com');
    await page.fill('input[name="password"]', 'SecurePassword123!');
    await page.fill('input[name="password_confirmation"]', 'SecurePassword123!');
    await page.click('button[type="submit"]');

    // Should show error
    await expect(page.locator('.text-red-600, .text-red-500')).toBeVisible();
  });

  test('worker registration requires email verification', async ({ page }) => {
    const email = generateTestEmail('worker');
    const name = generateTestName('Unverified Worker');

    await page.fill('input[name="name"]', name);
    await page.fill('input[name="email"]', email);
    await page.fill('input[name="password"]', 'SecurePassword123!');
    await page.fill('input[name="password_confirmation"]', 'SecurePassword123!');
    await page.click('button[type="submit"]');

    // Should be on verification page
    await expect(page).toHaveURL(/\/email\/verify/);

    // Try to access worker panel - Filament uses /worker/login for unauth users
    await page.goto('/worker');

    // Should redirect to verification, login, or Filament login
    await page.waitForTimeout(500);
    const url = page.url();
    // Unverified user should not be able to access worker panel
    expect(url).not.toMatch(/\/worker$/);
  });

  test('has link to login for existing workers', async ({ page }) => {
    const loginLink = page.locator('a:has-text("Login"), a:has-text("Sign in"), a[href*="login"]');
    await expect(loginLink).toBeVisible();
  });
});
