import { test, expect } from '@playwright/test';
import { generateTestEmail, generateTestName } from '../fixtures/test-helpers';

test.describe('Customer Registration', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('/register');
  });

  test('displays registration form', async ({ page }) => {
    await expect(page.locator('input[name="name"]')).toBeVisible();
    await expect(page.locator('input[name="email"]')).toBeVisible();
    await expect(page.locator('input[name="password"]')).toBeVisible();
    await expect(page.locator('input[name="password_confirmation"]')).toBeVisible();
    await expect(page.locator('button[type="submit"]')).toBeVisible();
  });

  test('has login link for existing users', async ({ page }) => {
    const loginLink = page.locator('a:has-text("Sign in"), a:has-text("Login"), a[href*="login"]');
    await expect(loginLink).toBeVisible();
  });

  test('customer can register with valid data', async ({ page }) => {
    const email = generateTestEmail('customer');
    const name = generateTestName('Test Customer');

    await page.fill('input[name="name"]', name);
    await page.fill('input[name="email"]', email);
    await page.fill('input[name="password"]', 'SecurePassword123!');
    await page.fill('input[name="password_confirmation"]', 'SecurePassword123!');
    await page.click('button[type="submit"]');

    // Should redirect to email verification
    await expect(page).toHaveURL(/\/email\/verify/);
  });

  test('registration fails with duplicate email', async ({ page }) => {
    // Use existing email
    await page.fill('input[name="name"]', 'Test User');
    await page.fill('input[name="email"]', 'miguel.torres@example.com');
    await page.fill('input[name="password"]', 'SecurePassword123!');
    await page.fill('input[name="password_confirmation"]', 'SecurePassword123!');
    await page.click('button[type="submit"]');

    // Should show error - Laravel says "The email has already been taken."
    await expect(page.locator('.text-red-600, .text-red-500')).toBeVisible();
  });

  test('registration fails with mismatched passwords', async ({ page }) => {
    const email = generateTestEmail('customer');

    await page.fill('input[name="name"]', 'Test User');
    await page.fill('input[name="email"]', email);
    await page.fill('input[name="password"]', 'Password123!');
    await page.fill('input[name="password_confirmation"]', 'DifferentPassword123!');
    await page.click('button[type="submit"]');

    // Should show password confirmation error
    await expect(page.locator('.text-red-600, .text-red-500')).toBeVisible();
  });

  test('registration fails with weak password', async ({ page }) => {
    const email = generateTestEmail('customer');

    await page.fill('input[name="name"]', 'Test User');
    await page.fill('input[name="email"]', email);
    await page.fill('input[name="password"]', '123');
    await page.fill('input[name="password_confirmation"]', '123');
    await page.click('button[type="submit"]');

    // Should show password requirements error
    await expect(page.locator('.text-red-600, .text-red-500')).toBeVisible();
  });

  test('registration fails with missing fields', async ({ page }) => {
    await page.click('button[type="submit"]');

    // HTML5 validation should prevent submission or show errors
    await expect(page).toHaveURL(/\/register/);
  });

  test('registration fails with invalid email format', async ({ page }) => {
    await page.fill('input[name="name"]', 'Test User');
    await page.fill('input[name="email"]', 'not-an-email');
    await page.fill('input[name="password"]', 'SecurePassword123!');
    await page.fill('input[name="password_confirmation"]', 'SecurePassword123!');
    await page.click('button[type="submit"]');

    // Should stay on page or show error
    await expect(page).toHaveURL(/\/register/);
  });

  test('newly registered user requires email verification', async ({ page }) => {
    const email = generateTestEmail('customer');
    const name = generateTestName('Test Customer');

    await page.fill('input[name="name"]', name);
    await page.fill('input[name="email"]', email);
    await page.fill('input[name="password"]', 'SecurePassword123!');
    await page.fill('input[name="password_confirmation"]', 'SecurePassword123!');
    await page.click('button[type="submit"]');

    // Should be on verification page
    await expect(page).toHaveURL(/\/email\/verify/);
    await expect(page.locator('h1:has-text("Verify Your Email")')).toBeVisible();

    // Try to access customer dashboard
    await page.goto('/customer/dashboard');

    // Should redirect back to verification
    await expect(page).toHaveURL(/\/email\/verify/);
  });
});
