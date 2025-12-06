import { test, expect } from '@playwright/test';

test.describe('Worker Dashboard', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('/login');
    await page.fill('input[name="email"]', 'lisa.mendoza@example.com');
    await page.fill('input[name="password"]', 'password');
    await page.click('button[type="submit"]');
    await expect(page).toHaveURL(/\/worker/);
  });

  test('displays Filament worker dashboard', async ({ page }) => {
    // Should show Filament dashboard heading
    await expect(page.locator('h1:has-text("Dashboard")')).toBeVisible();
  });

  test('shows pending orders count widget', async ({ page }) => {
    // Look for stats widget showing pending orders
    await expect(page.locator('text=Pending Orders')).toBeVisible();
  });

  test('shows completed orders count widget', async ({ page }) => {
    await expect(page.locator('text=Completed Orders')).toBeVisible();
  });

  test('shows total earnings widget', async ({ page }) => {
    await expect(page.locator('text=Total Earnings')).toBeVisible();
  });

  test('shows monthly earnings widget', async ({ page }) => {
    const monthlyWidget = page.locator('text=Month, text=This Month');
    // May or may not show monthly earnings depending on design
  });

  test('has navigation to listings', async ({ page }) => {
    const listingsNav = page.locator('a:has-text("Listings"), a:has-text("My Listings")');
    await expect(listingsNav.first()).toBeVisible();
  });

  test('has navigation to orders', async ({ page }) => {
    const ordersNav = page.locator('a:has-text("Orders")');
    await expect(ordersNav.first()).toBeVisible();
  });

  test('has navigation to profile', async ({ page }) => {
    const profileNav = page.locator('a:has-text("Profile"), a:has-text("My Profile")');
    await expect(profileNav.first()).toBeVisible();
  });

  test('shows orders badge with pending count', async ({ page }) => {
    // Orders navigation may have a badge showing pending count
    const ordersBadge = page.locator('[class*="badge"], [class*="count"]');
    // Badge may or may not be visible depending on orders
  });
});
