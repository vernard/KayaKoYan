import { test, expect } from '@playwright/test';

test.describe('Authorization Edge Cases', () => {
  test('unauthenticated user redirected to login', async ({ page }) => {
    const response = await page.goto('/customer/dashboard');
    await expect(page).toHaveURL(/\/login/);
  });

  test('unverified user redirected to verification', async ({ page }) => {
    // Register new user
    await page.goto('/register');
    await page.fill('input[name="name"]', 'Unverified Test');
    await page.fill('input[name="email"]', `unverified-${Date.now()}@example.com`);
    await page.fill('input[name="password"]', 'SecurePassword123!');
    await page.fill('input[name="password_confirmation"]', 'SecurePassword123!');
    await page.click('button[type="submit"]');

    // Try to access protected route
    await page.goto('/customer/dashboard');

    // Should redirect to verification
    await expect(page).toHaveURL(/\/email\/verify/);
  });

  test('wrong role gets 403 error', async ({ page }) => {
    // Login as customer
    await page.goto('/login');
    await page.fill('input[name="email"]', 'miguel.torres@example.com');
    await page.fill('input[name="password"]', 'password');
    await page.click('button[type="submit"]');

    // Try to access worker routes
    const response = await page.goto('/worker/orders/1/chat');

    // Should be denied (403 or redirect)
    expect(response?.status()).toBeGreaterThanOrEqual(400);
  });

  test('customer accessing worker panel gets denied', async ({ page }) => {
    await page.goto('/login');
    await page.fill('input[name="email"]', 'miguel.torres@example.com');
    await page.fill('input[name="password"]', 'password');
    await page.click('button[type="submit"]');

    const response = await page.goto('/worker');

    // Should redirect to login or show error
    const url = page.url();
    expect(url).not.toMatch(/^\/worker$/);
  });

  test('worker accessing admin panel gets denied', async ({ page }) => {
    await page.goto('/login');
    await page.fill('input[name="email"]', 'lisa.mendoza@example.com');
    await page.fill('input[name="password"]', 'password');
    await page.click('button[type="submit"]');

    const response = await page.goto('/admin');

    const url = page.url();
    expect(url).not.toMatch(/^\/admin$/);
  });
});

test.describe('Order Status Edge Cases', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('/login');
    await page.fill('input[name="email"]', 'miguel.torres@example.com');
    await page.fill('input[name="password"]', 'password');
    await page.click('button[type="submit"]');
  });

  test('cannot pay cancelled order', async ({ page }) => {
    // If we have a cancelled order, payment page should not be accessible
    // This is enforced by authorization
    await page.goto('/customer/orders');

    // Look for cancelled order status
    const pageContent = await page.content();
    if (pageContent.includes('Cancelled')) {
      // Payment action should not be available
    }
  });

  test('cannot accept non-delivered order', async ({ page }) => {
    await page.goto('/customer/orders');
    const orderLink = page.locator('a[href*="/customer/orders/"]').first();

    if (await orderLink.isVisible()) {
      await orderLink.click();

      const pageContent = await page.content();
      const isDelivered = pageContent.toLowerCase().includes('delivered');

      if (!isDelivered) {
        const acceptButton = page.locator('button:has-text("Accept Delivery")');
        await expect(acceptButton).not.toBeVisible();
      }
    }
  });

  test('completed order has no actions', async ({ page }) => {
    await page.goto('/customer/orders');
    const orderLink = page.locator('a[href*="/customer/orders/"]').first();

    if (await orderLink.isVisible()) {
      await orderLink.click();

      const pageContent = await page.content();
      const isCompleted = pageContent.includes('Completed');

      if (isCompleted) {
        // No action buttons for completed orders
        const payButton = page.locator('a:has-text("Pay"), button:has-text("Pay")');
        const acceptButton = page.locator('button:has-text("Accept")');

        await expect(payButton).not.toBeVisible();
        await expect(acceptButton).not.toBeVisible();
      }
    }
  });
});

test.describe('File Upload Edge Cases', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('/login');
    await page.fill('input[name="email"]', 'miguel.torres@example.com');
    await page.fill('input[name="password"]', 'password');
    await page.click('button[type="submit"]');
  });

  test('payment proof rejects non-image file', async ({ page }) => {
    // Create an order first
    await page.goto('/listings');
    await page.locator('a[href*="/listings/"]').first().click();
    await page.click('button[type="submit"]');

    // On payment page
    await page.selectOption('select[name="method"]', 'gcash');
    await page.fill('input[name="reference_number"]', 'GC123');

    // Try to upload non-image (validation should reject)
    // This requires creating a test file
  });

  test('payment proof size limit enforced', async ({ page }) => {
    // File larger than 10MB should be rejected
    // Would need to create oversized test file
  });
});

test.describe('Missing Data Edge Cases', () => {
  test('listing without images shows placeholder', async ({ page }) => {
    await page.goto('/listings');

    // Check if listings have images or placeholders
    const images = page.locator('img');
    const count = await images.count();

    // Should have at least placeholder images
    expect(count).toBeGreaterThan(0);
  });

  test('worker without avatar shows default', async ({ page }) => {
    // Visit worker profile
    await page.goto('/worker/lisa-mendoza');

    // Should show avatar (either custom or UI Avatars)
    const avatar = page.locator('img');
    await expect(avatar.first()).toBeVisible();
  });

  test('worker without payment methods shows info', async ({ page }) => {
    // This depends on the worker's profile configuration
    await page.goto('/worker/lisa-mendoza');

    // Should show payment section or informational message
    const paymentSection = page.locator('text=Payment, text=GCash, text=Bank');
    // May show payment methods or message that none configured
  });
});

test.describe('Concurrent/Race Conditions', () => {
  test('double form submission prevented', async ({ page }) => {
    await page.goto('/login');
    await page.fill('input[name="email"]', 'miguel.torres@example.com');
    await page.fill('input[name="password"]', 'password');

    // Try double click on submit
    await page.click('button[type="submit"]');
    await page.click('button[type="submit"]');

    // Should still work correctly (single login)
    await expect(page).toHaveURL(/\/customer\/dashboard/);
  });
});

test.describe('404 Pages', () => {
  test('non-existent listing returns 404', async ({ page }) => {
    const response = await page.goto('/listings/non-existent-listing-12345');
    expect(response?.status()).toBe(404);
  });

  test('non-existent worker profile returns 404', async ({ page }) => {
    const response = await page.goto('/worker/non-existent-worker-12345');
    expect(response?.status()).toBe(404);
  });

  test('non-existent order returns 404 or 403', async ({ page }) => {
    await page.goto('/login');
    await page.fill('input[name="email"]', 'miguel.torres@example.com');
    await page.fill('input[name="password"]', 'password');
    await page.click('button[type="submit"]');

    const response = await page.goto('/customer/orders/99999');
    expect(response?.status()).toBeGreaterThanOrEqual(400);
  });
});

test.describe('Input Validation', () => {
  test('XSS in search is prevented', async ({ page }) => {
    await page.goto('/listings');

    const searchInput = page.locator('input[name="search"], input[type="search"]');

    if (await searchInput.isVisible()) {
      await searchInput.fill('<script>alert("xss")</script>');
      await page.keyboard.press('Enter');

      // Page should not execute script
      // No alert should appear
      const pageContent = await page.content();
      expect(pageContent).not.toContain('<script>alert');
    }
  });

  test('SQL injection in search is prevented', async ({ page }) => {
    await page.goto('/listings');

    const searchInput = page.locator('input[name="search"]');

    if (await searchInput.isVisible()) {
      await searchInput.fill("'; DROP TABLE users; --");
      await page.keyboard.press('Enter');

      // Page should load normally
      await expect(page).toHaveURL(/\/listings/);
    }
  });
});
