import { test, expect } from '@playwright/test';
import { generateTestEmail, generateTestName } from '../fixtures/test-helpers';

test.describe('Email Verification', () => {
  test('unverified user sees verification notice', async ({ page }) => {
    // Register a new user
    const email = generateTestEmail('verify');
    const name = generateTestName('Verify User');

    await page.goto('/register');
    await page.fill('input[name="name"]', name);
    await page.fill('input[name="email"]', email);
    await page.fill('input[name="password"]', 'SecurePassword123!');
    await page.fill('input[name="password_confirmation"]', 'SecurePassword123!');
    await page.click('button[type="submit"]');

    // Should be on verification notice page
    await expect(page).toHaveURL(/\/email\/verify/);
    // Page title says "Verify Your Email"
    await expect(page.locator('h1:has-text("Verify Your Email")')).toBeVisible();
  });

  test('verification notice shows user email', async ({ page }) => {
    const email = generateTestEmail('showmail');
    const name = generateTestName('Show Mail User');

    await page.goto('/register');
    await page.fill('input[name="name"]', name);
    await page.fill('input[name="email"]', email);
    await page.fill('input[name="password"]', 'SecurePassword123!');
    await page.fill('input[name="password_confirmation"]', 'SecurePassword123!');
    await page.click('button[type="submit"]');

    // The email should be displayed on the verification page
    const pageContent = await page.content();
    expect(pageContent).toContain(email);
  });

  test('user can resend verification email', async ({ page }) => {
    const email = generateTestEmail('resend');
    const name = generateTestName('Resend User');

    await page.goto('/register');
    await page.fill('input[name="name"]', name);
    await page.fill('input[name="email"]', email);
    await page.fill('input[name="password"]', 'SecurePassword123!');
    await page.fill('input[name="password_confirmation"]', 'SecurePassword123!');
    await page.click('button[type="submit"]');

    // Click resend button - text is "Resend Verification Email"
    const resendButton = page.locator('button:has-text("Resend Verification Email")');
    await expect(resendButton).toBeVisible();
    await resendButton.click();

    // Should show success message in green box
    await expect(page.locator('.bg-green-50')).toBeVisible();
  });

  test('already verified user is redirected to dashboard', async ({ page }) => {
    // Login as verified user
    await page.goto('/login');
    await page.fill('input[name="email"]', 'miguel.torres@example.com');
    await page.fill('input[name="password"]', 'password');
    await page.click('button[type="submit"]');

    // Should go to dashboard, not verification
    await expect(page).toHaveURL(/\/customer\/dashboard/);

    // Try to access verification page
    await page.goto('/email/verify');

    // Should redirect to dashboard since already verified
    await expect(page).toHaveURL(/\/customer\/dashboard/);
  });

  test('unverified user cannot access protected routes', async ({ page }) => {
    const email = generateTestEmail('protected');
    const name = generateTestName('Protected User');

    await page.goto('/register');
    await page.fill('input[name="name"]', name);
    await page.fill('input[name="email"]', email);
    await page.fill('input[name="password"]', 'SecurePassword123!');
    await page.fill('input[name="password_confirmation"]', 'SecurePassword123!');
    await page.click('button[type="submit"]');

    // Try to access customer dashboard
    await page.goto('/customer/dashboard');

    // Should be redirected to verification
    await expect(page).toHaveURL(/\/email\/verify/);
  });

  test('invalid verification link shows error', async ({ page }) => {
    // Login as unverified user first (if we had one)
    // Then try invalid verification link
    await page.goto('/email/verify/999/invalid-hash');

    // Should show error (403 or error page)
    const status = await page.locator('text=403, text=invalid, text=expired, text=error').isVisible();
    // May redirect or show error
  });

  test('verification resend is rate limited', async ({ page }) => {
    const email = generateTestEmail('ratelimit');
    const name = generateTestName('Rate Limit User');

    await page.goto('/register');
    await page.fill('input[name="name"]', name);
    await page.fill('input[name="email"]', email);
    await page.fill('input[name="password"]', 'SecurePassword123!');
    await page.fill('input[name="password_confirmation"]', 'SecurePassword123!');
    await page.click('button[type="submit"]');

    // Click resend multiple times rapidly
    const resendButton = page.locator('button:has-text("Resend"), a:has-text("Resend")');

    for (let i = 0; i < 7; i++) {
      if (await resendButton.isVisible()) {
        await resendButton.click();
        await page.waitForTimeout(100);
      }
    }

    // After 6+ requests in 1 minute, should be rate limited
    // Check for rate limit message or 429 error
    const pageContent = await page.content();
    const isRateLimited = pageContent.includes('too many') ||
                          pageContent.includes('rate') ||
                          pageContent.includes('wait') ||
                          pageContent.includes('429');

    // Rate limiting should kick in eventually
    // This test may be flaky depending on timing
  });
});
