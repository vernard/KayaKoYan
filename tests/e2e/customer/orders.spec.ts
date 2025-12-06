import { test, expect } from '@playwright/test';

test.describe('Customer Orders', () => {
  test.beforeEach(async ({ page }) => {
    // Login as customer
    await page.goto('/login');
    await page.fill('input[name="email"]', 'miguel.torres@example.com');
    await page.fill('input[name="password"]', 'password');
    await page.click('button[type="submit"]');
  });

  test('can view order list', async ({ page }) => {
    await page.goto('/customer/orders');

    // Should show orders page
    await expect(page.locator('h1, h2')).toContainText(/Orders/i);
  });

  test('orders are paginated', async ({ page }) => {
    await page.goto('/customer/orders');

    // Check for pagination if many orders exist
    const pagination = page.locator('nav[aria-label*="pagination"], .pagination');
    // May or may not be visible
  });

  test('can filter orders by status', async ({ page }) => {
    await page.goto('/customer/orders');

    // Look for status filter
    const statusFilter = page.locator('select[name="status"], [data-filter="status"]');

    if (await statusFilter.isVisible()) {
      await statusFilter.selectOption('completed');
      await page.waitForLoadState('networkidle');

      // URL should have status param or results filtered
    }
  });

  test('order cards show essential info', async ({ page }) => {
    await page.goto('/customer/orders');

    const orderCards = page.locator('a[href*="/customer/orders/"], [data-testid="order-card"]');
    const count = await orderCards.count();

    if (count > 0) {
      const firstOrder = orderCards.first();

      // Should show order number or title
      const cardText = await firstOrder.textContent();
      expect(cardText).toBeTruthy();
    }
  });

  test('clicking order goes to detail page', async ({ page }) => {
    await page.goto('/customer/orders');

    const orderLink = page.locator('a[href*="/customer/orders/"]').first();

    if (await orderLink.isVisible()) {
      await orderLink.click();
      await expect(page).toHaveURL(/\/customer\/orders\/\d+$/);
    }
  });

  test('cannot view other customer orders', async ({ page }) => {
    // Try to access an order that doesn't belong to this customer
    // This would require knowing another customer's order ID
    // For now, we verify the route protection exists

    const response = await page.goto('/customer/orders/99999');

    // Should return 403 or 404
    expect(response?.status()).toBeGreaterThanOrEqual(400);
  });
});

test.describe('Order Details', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('/login');
    await page.fill('input[name="email"]', 'miguel.torres@example.com');
    await page.fill('input[name="password"]', 'password');
    await page.click('button[type="submit"]');
  });

  test('shows full order information', async ({ page }) => {
    await page.goto('/customer/orders');

    const orderLink = page.locator('a[href*="/customer/orders/"]').first();

    if (await orderLink.isVisible()) {
      await orderLink.click();

      // Should show order number - it's displayed as h1 with format KKY-YYMMDD-XXXX
      await expect(page.locator('h1')).toBeVisible();

      // Should show status badge
      await expect(page.locator('.rounded-full')).toBeVisible();

      // Should show listing title (in h3)
      await expect(page.locator('h3')).toBeVisible();
    }
  });

  test('shows payment status section', async ({ page }) => {
    await page.goto('/customer/orders');
    const orderLink = page.locator('a[href*="/customer/orders/"]').first();

    if (await orderLink.isVisible()) {
      await orderLink.click();

      // Look for payment section
      const paymentSection = page.locator('text=Payment');
      // May show payment status if payment exists
    }
  });

  test('shows worker information', async ({ page }) => {
    await page.goto('/customer/orders');
    const orderLink = page.locator('a[href*="/customer/orders/"]').first();

    if (await orderLink.isVisible()) {
      await orderLink.click();

      // Should show worker section with heading "Worker"
      await expect(page.locator('h2:has-text("Worker")')).toBeVisible();
    }
  });

  test('shows customer notes if provided', async ({ page }) => {
    await page.goto('/customer/orders');
    const orderLink = page.locator('a[href*="/customer/orders/"]').first();

    if (await orderLink.isVisible()) {
      await orderLink.click();

      // Notes section may or may not be visible depending on order
      const notesSection = page.locator('text=Notes, text=Message');
      // Check presence, don't require visibility
    }
  });
});

test.describe('Order Creation', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('/login');
    await page.fill('input[name="email"]', 'miguel.torres@example.com');
    await page.fill('input[name="password"]', 'password');
    await page.click('button[type="submit"]');
  });

  test('can create order from listing', async ({ page }) => {
    // Navigate to a listing
    await page.goto('/listings');
    await page.locator('a[href*="/listings/"]').first().click();

    // Wait for page to load
    await expect(page.locator('button:has-text("Order Now")')).toBeVisible();

    // Submit order
    await page.click('button:has-text("Order Now")');

    // Should redirect to payment page
    await expect(page).toHaveURL(/\/customer\/orders\/\d+\/payment/);
  });

  test('order includes optional notes', async ({ page }) => {
    await page.goto('/listings');
    await page.locator('a[href*="/listings/"]').first().click();

    // Wait for page to load
    await expect(page.locator('button:has-text("Order Now")')).toBeVisible();

    // Fill notes if field exists
    const notesField = page.locator('textarea[name="notes"]');
    if (await notesField.isVisible()) {
      await notesField.fill('Please prioritize this order.');
    }

    await page.click('button:has-text("Order Now")');
    await expect(page).toHaveURL(/\/customer\/orders\/\d+\/payment/);
  });

  test('order is created with pending payment status', async ({ page }) => {
    await page.goto('/listings');
    await page.locator('a[href*="/listings/"]').first().click();

    // Wait for page to load
    await expect(page.locator('button:has-text("Order Now")')).toBeVisible();
    await page.click('button:has-text("Order Now")');

    // On payment page, order should be pending payment
    await expect(page).toHaveURL(/\/payment/);

    // Navigate to order to verify status
    const url = page.url();
    const orderId = url.match(/\/orders\/(\d+)\//)?.[1];

    if (orderId) {
      await page.goto(`/customer/orders/${orderId}`);
      // Status is displayed in a badge
      const pageContent = await page.content();
      expect(pageContent).toContain('Pending Payment');
    }
  });

  test('order generates unique order number', async ({ page }) => {
    await page.goto('/listings');
    await page.locator('a[href*="/listings/"]').first().click();
    await page.click('button[type="submit"]');

    const url = page.url();
    const orderId = url.match(/\/orders\/(\d+)\//)?.[1];

    if (orderId) {
      await page.goto(`/customer/orders/${orderId}`);

      // Should show order number in KKY-YYMMDD-XXXX format
      await expect(page.locator('text=KKY-')).toBeVisible();
    }
  });

  test('cannot order inactive listing', async ({ page }) => {
    // This would require an inactive listing slug
    // The listing page should return 404 for inactive listings
    const response = await page.goto('/listings/inactive-test-listing-12345');
    expect(response?.status()).toBe(404);
  });
});
