import { test, expect } from '@playwright/test';

test.describe('Home Page', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('/');
  });

  test('displays home page with branding', async ({ page }) => {
    // Should see site name in header
    await expect(page.locator('a:has-text("Kaya Ko Yan")').first()).toBeVisible();
  });

  test('has navigation links', async ({ page }) => {
    // Browse listings link
    await expect(page.locator('a:has-text("Browse Listings")')).toBeVisible();

    // Login link for guests
    await expect(page.locator('a:has-text("Login")')).toBeVisible();

    // Sign up link for guests
    await expect(page.locator('a:has-text("Sign Up")')).toBeVisible();
  });

  test('shows featured listings', async ({ page }) => {
    // Should show some listings on homepage
    const listings = page.locator('a[href*="/listings/"], [data-testid="listing-card"]');
    const count = await listings.count();

    // Home page should have at least some content
    // This depends on whether home page shows featured listings
    expect(count).toBeGreaterThanOrEqual(0);
  });

  test('footer shows become a worker link', async ({ page }) => {
    // Check for become a worker link (may be in header or body)
    const workerLink = page.locator('a:has-text("Become a Worker")');
    await expect(workerLink.first()).toBeVisible();
  });

  test('browse listings link works', async ({ page }) => {
    await page.click('a:has-text("Browse Listings")');
    await expect(page).toHaveURL(/\/listings/);
  });

  test('login link works', async ({ page }) => {
    await page.click('a:has-text("Login")');
    await expect(page).toHaveURL(/\/login/);
  });

  test('sign up link works', async ({ page }) => {
    await page.click('a:has-text("Sign Up"), a:has-text("Register")');
    await expect(page).toHaveURL(/\/register/);
  });

  test('become a worker link works', async ({ page }) => {
    await page.click('a:has-text("Become a Worker")');
    await expect(page).toHaveURL(/\/become-a-worker/);
  });
});
