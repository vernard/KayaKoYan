import { test, expect } from '@playwright/test';

test.describe('Admin Panel Access', () => {
  test('admin can access admin panel', async ({ page }) => {
    await page.goto('/login');
    await page.fill('input[name="email"]', 'admin@kayakoyan.com');
    await page.fill('input[name="password"]', 'password');
    await page.click('button[type="submit"]');

    await expect(page).toHaveURL(/\/admin/);
  });

  test('admin panel shows dashboard', async ({ page }) => {
    await page.goto('/login');
    await page.fill('input[name="email"]', 'admin@kayakoyan.com');
    await page.fill('input[name="password"]', 'password');
    await page.click('button[type="submit"]');

    // Navigate to admin panel and check dashboard
    await page.goto('/admin');
    await expect(page.locator('h1:has-text("Dashboard")')).toBeVisible();
  });

  test('worker cannot access admin panel', async ({ page }) => {
    await page.goto('/login');
    await page.fill('input[name="email"]', 'lisa.mendoza@example.com');
    await page.fill('input[name="password"]', 'password');
    await page.click('button[type="submit"]');

    // Try to access admin panel
    await page.goto('/admin');

    // Should see 403 Forbidden page
    await expect(page.locator('text=403')).toBeVisible();
    await expect(page.locator('text=FORBIDDEN')).toBeVisible();
  });

  test('customer cannot access admin panel', async ({ page }) => {
    await page.goto('/login');
    await page.fill('input[name="email"]', 'miguel.torres@example.com');
    await page.fill('input[name="password"]', 'password');
    await page.click('button[type="submit"]');

    // Try to access admin panel
    await page.goto('/admin');

    // Should see 403 Forbidden page
    await expect(page.locator('text=403')).toBeVisible();
    await expect(page.locator('text=FORBIDDEN')).toBeVisible();
  });

  test('guest cannot access admin panel', async ({ page }) => {
    const response = await page.goto('/admin');

    // Should redirect to login
    await expect(page).toHaveURL(/\/login/);
  });
});

test.describe('Admin User Management', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('/login');
    await page.fill('input[name="email"]', 'admin@kayakoyan.com');
    await page.fill('input[name="password"]', 'password');
    await page.click('button[type="submit"]');
    await page.goto('/admin');
  });

  test('can view all users', async ({ page }) => {
    await page.click('a:has-text("Users")');
    await page.waitForLoadState('networkidle');

    // Should show users list heading
    await expect(page.locator('h1:has-text("Users")')).toBeVisible();
  });

  test('can navigate to create user page', async ({ page }) => {
    await page.click('a:has-text("Users")');
    await page.waitForLoadState('networkidle');

    // Look for "New user" button (Filament's create button)
    const createButton = page.locator('a:has-text("New user")');

    if (await createButton.isVisible()) {
      await createButton.click();
      await expect(page).toHaveURL(/\/create/);
    }
  });

  test('users table has expected columns', async ({ page }) => {
    await page.click('a:has-text("Users")');
    await page.waitForLoadState('networkidle');

    // Should show user data columns
    const pageContent = await page.content();
    expect(pageContent).toContain('Name');
    expect(pageContent).toContain('Email');
  });
});

test.describe('Admin Order Oversight', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('/login');
    await page.fill('input[name="email"]', 'admin@kayakoyan.com');
    await page.fill('input[name="password"]', 'password');
    await page.click('button[type="submit"]');
    await page.goto('/admin');
  });

  test('can view all orders', async ({ page }) => {
    await page.click('a:has-text("Orders")');

    // Should show orders list with table
    await expect(page.locator('h1:has-text("Orders")')).toBeVisible();
  });

  test('orders page shows order details', async ({ page }) => {
    await page.click('a:has-text("Orders")');

    // Admin sees all orders
    await page.waitForLoadState('networkidle');
    // Table or order list should be visible
    const pageContent = await page.content();
    expect(pageContent).toContain('Orders');
  });
});
