import { test, expect } from '@playwright/test';

test.describe('Login', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('/login');
  });

  test('displays login form', async ({ page }) => {
    await expect(page.locator('h1, h2')).toContainText(/Welcome Back|Login|Sign In/i);
    await expect(page.locator('input[name="email"]')).toBeVisible();
    await expect(page.locator('input[name="password"]')).toBeVisible();
    await expect(page.locator('button[type="submit"]')).toBeVisible();
  });

  test('has forgot password link', async ({ page }) => {
    const forgotLink = page.locator('a:has-text("Forgot"), a[href*="forgot"]');
    await expect(forgotLink).toBeVisible();
  });

  test('has register link', async ({ page }) => {
    const registerLink = page.locator('a:has-text("Sign up"), a[href*="register"]');
    await expect(registerLink).toBeVisible();
  });

  test('has become a worker link', async ({ page }) => {
    const workerLink = page.locator('a:has-text("Become a Worker"), a[href*="become-a-worker"]');
    await expect(workerLink).toBeVisible();
  });

  test('customer can login with valid credentials', async ({ page }) => {
    await page.fill('input[name="email"]', 'miguel.torres@example.com');
    await page.fill('input[name="password"]', 'password');
    await page.click('button[type="submit"]');

    await expect(page).toHaveURL(/\/customer\/dashboard/);
  });

  test('worker can login with valid credentials', async ({ page }) => {
    await page.fill('input[name="email"]', 'lisa.mendoza@example.com');
    await page.fill('input[name="password"]', 'password');
    await page.click('button[type="submit"]');

    await expect(page).toHaveURL(/\/worker/);
  });

  test('admin can login with valid credentials', async ({ page }) => {
    await page.fill('input[name="email"]', 'admin@kayakoyan.com');
    await page.fill('input[name="password"]', 'password');
    await page.click('button[type="submit"]');

    await expect(page).toHaveURL(/\/admin/);
  });

  test('login fails with invalid credentials', async ({ page }) => {
    await page.fill('input[name="email"]', 'wrong@example.com');
    await page.fill('input[name="password"]', 'wrongpassword');
    await page.click('button[type="submit"]');

    // Should stay on login page with error
    await expect(page).toHaveURL(/\/login/);
    await expect(page.locator('.text-red-600, .text-red-500, [class*="error"]')).toBeVisible();
  });

  test('login fails with empty fields', async ({ page }) => {
    await page.click('button[type="submit"]');

    // Should show validation errors or stay on page
    await expect(page).toHaveURL(/\/login/);
  });

  test('remember me checkbox exists', async ({ page }) => {
    const rememberCheckbox = page.locator('input[name="remember"]');
    await expect(rememberCheckbox).toBeVisible();
  });

  test('user can logout', async ({ page }) => {
    // First login
    await page.fill('input[name="email"]', 'miguel.torres@example.com');
    await page.fill('input[name="password"]', 'password');
    await page.click('button[type="submit"]');

    await expect(page).toHaveURL(/\/customer\/dashboard/);

    // Find and click logout - form submits to /logout
    const logoutButton = page.locator('form[action*="logout"] button, button:has-text("Logout")');
    await expect(logoutButton).toBeVisible();
    await logoutButton.click();

    // Should redirect to home or login
    await page.waitForURL(/^\/$|\/login/);
  });
});
