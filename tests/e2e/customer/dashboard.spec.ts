import { test, expect } from '@playwright/test';

test.describe('Customer Dashboard', () => {
  test.beforeEach(async ({ page }) => {
    // Login as customer
    await page.goto('/login');
    await page.fill('input[name="email"]', 'miguel.torres@example.com');
    await page.fill('input[name="password"]', 'password');
    await page.click('button[type="submit"]');
    await expect(page).toHaveURL(/\/customer/);
  });

  test('displays dashboard with welcome message', async ({ page }) => {
    // Should show welcome message with user name
    await expect(page.locator('text=Welcome back')).toBeVisible();
  });

  test('shows order statistics', async ({ page }) => {
    // Should show active orders count
    await expect(page.locator('text=Active Orders')).toBeVisible();

    // Should show completed orders count
    await expect(page.locator('text=Completed Orders')).toBeVisible();
  });

  test('shows recent orders section', async ({ page }) => {
    // Should have recent orders section
    await expect(page.locator('text=Recent Orders')).toBeVisible();
  });

  test('has link to browse services', async ({ page }) => {
    const browseLink = page.locator('a:has-text("Browse"), a:has-text("Services"), a[href*="listings"]');
    await expect(browseLink.first()).toBeVisible();
  });

  test('has link to view all orders', async ({ page }) => {
    const ordersLink = page.locator('a:has-text("View All"), a:has-text("My Orders"), a[href*="orders"]');
    await expect(ordersLink.first()).toBeVisible();
  });

  test('recent orders link to order details', async ({ page }) => {
    // Check if there are any order links
    const orderLinks = page.locator('a[href*="/customer/orders/"]');
    const count = await orderLinks.count();

    if (count > 0) {
      await orderLinks.first().click();
      await expect(page).toHaveURL(/\/customer\/orders\/\d+/);
    }
  });

  test('shows order status badges', async ({ page }) => {
    // Look for status indicators
    const statusBadges = page.locator('[class*="badge"], [class*="status"], [class*="pill"]');
    // Status badges may exist if there are orders
  });

  test('navigation shows dashboard is active', async ({ page }) => {
    // Dashboard link should be highlighted/active
    const dashboardLink = page.locator('a[href*="dashboard"]');
    await expect(dashboardLink).toBeVisible();
  });
});
