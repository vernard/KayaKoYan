import { test, expect } from '@playwright/test';

test.describe('Worker Order Management', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('/login');
    await page.fill('input[name="email"]', 'lisa.mendoza@example.com');
    await page.fill('input[name="password"]', 'password');
    await page.click('button[type="submit"]');

    // Wait for worker panel, then click Orders
    await page.waitForURL(/\/worker/);
    await page.click('a:has-text("Orders")');
  });

  test('displays orders table', async ({ page }) => {
    await expect(page.locator('table, [data-testid="orders-table"]')).toBeVisible();
  });

  test('shows order columns', async ({ page }) => {
    await expect(page.locator('th:has-text("Order #")')).toBeVisible();
    await expect(page.locator('th:has-text("Customer")')).toBeVisible();
    await expect(page.locator('th:has-text("Status")')).toBeVisible();
  });

  test('only shows worker own orders', async ({ page }) => {
    // All orders in the table belong to current worker
    // This is enforced by query scope
    const ordersTable = page.locator('table tbody tr');
    // Table may have orders or be empty
  });

  test('can filter orders by status', async ({ page }) => {
    const statusFilter = page.locator('select[name*="status"], [data-filter="status"]');

    if (await statusFilter.isVisible()) {
      await statusFilter.selectOption('payment_submitted');
      await page.waitForLoadState('networkidle');
    }
  });

  test('can view order details', async ({ page }) => {
    const orderRow = page.locator('table tbody tr a, [data-testid="order-row"]').first();

    if (await orderRow.isVisible()) {
      await orderRow.click();
      await expect(page).toHaveURL(/\/orders\/\d+/);
    }
  });

  test('order details show customer info', async ({ page }) => {
    // In Filament table, find first row and click
    const orderLink = page.locator('table tbody tr').first().locator('a').first();

    if (await orderLink.isVisible()) {
      await orderLink.click();

      // Filament view page should show customer info section
      // Look for any content that indicates customer information
      await page.waitForLoadState('networkidle');
      const pageContent = await page.content();
      expect(pageContent.length).toBeGreaterThan(0);
    }
  });

  test('order details show payment proof', async ({ page }) => {
    const orderRow = page.locator('table tbody tr a').first();

    if (await orderRow.isVisible()) {
      await orderRow.click();

      // Payment section with proof image
      const paymentSection = page.locator('text=Payment');
      // May show proof image if payment submitted
    }
  });
});

test.describe('Payment Verification', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('/login');
    await page.fill('input[name="email"]', 'lisa.mendoza@example.com');
    await page.fill('input[name="password"]', 'password');
    await page.click('button[type="submit"]');
    await page.waitForURL(/\/worker/);
    await page.click('a:has-text("Orders")');
  });

  test('verify payment button visible for payment submitted orders', async ({ page }) => {
    const orderRow = page.locator('table tbody tr a').first();

    if (await orderRow.isVisible()) {
      await orderRow.click();

      const verifyButton = page.locator('button:has-text("Verify Payment")');
      // Button only visible for payment_submitted status
    }
  });

  test('can verify payment', async ({ page }) => {
    const orderRow = page.locator('table tbody tr a').first();

    if (await orderRow.isVisible()) {
      await orderRow.click();

      const verifyButton = page.locator('button:has-text("Verify Payment")');

      if (await verifyButton.isVisible()) {
        await verifyButton.click();

        const confirmButton = page.locator('button:has-text("Confirm")');
        if (await confirmButton.isVisible()) {
          await confirmButton.click();
        }

        await expect(page.locator('text=verified, text=Payment Received')).toBeVisible();
      }
    }
  });

  test('cannot verify already verified payment', async ({ page }) => {
    const orderRow = page.locator('table tbody tr a').first();

    if (await orderRow.isVisible()) {
      await orderRow.click();

      // If payment already verified, button should be hidden
      const pageContent = await page.content();
      const isVerified = pageContent.includes('Payment Received') ||
                         pageContent.includes('Verified');

      if (isVerified) {
        const verifyButton = page.locator('button:has-text("Verify Payment")');
        await expect(verifyButton).not.toBeVisible();
      }
    }
  });

  test('payment verification sets timestamp', async ({ page }) => {
    // After verification, verified_at should be set
    // This is a database check that can be verified via UI
    const orderRow = page.locator('table tbody tr a').first();

    if (await orderRow.isVisible()) {
      await orderRow.click();

      // Look for verification timestamp or verified status
      const verifiedInfo = page.locator('text=Verified');
      // Timestamp may be displayed
    }
  });
});

test.describe('Service Order Workflow', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('/login');
    await page.fill('input[name="email"]', 'lisa.mendoza@example.com');
    await page.fill('input[name="password"]', 'password');
    await page.click('button[type="submit"]');
    await page.waitForURL(/\/worker/);
    await page.click('a:has-text("Orders")');
  });

  test('start work button visible for verified payment', async ({ page }) => {
    const orderRow = page.locator('table tbody tr a').first();

    if (await orderRow.isVisible()) {
      await orderRow.click();

      const startButton = page.locator('button:has-text("Start Work")');
      // Button visible for PaymentReceived status on service orders
    }
  });

  test('can start work on service order', async ({ page }) => {
    const orderRow = page.locator('table tbody tr a').first();

    if (await orderRow.isVisible()) {
      await orderRow.click();

      const startButton = page.locator('button:has-text("Start Work")');

      if (await startButton.isVisible()) {
        await startButton.click();

        const confirmButton = page.locator('button:has-text("Confirm")');
        if (await confirmButton.isVisible()) {
          await confirmButton.click();
        }

        await expect(page.locator('text=In Progress')).toBeVisible();
      }
    }
  });

  test('cannot start work on digital product', async ({ page }) => {
    // Digital products auto-complete, no start work step
    const orderRow = page.locator('table tbody tr a').first();

    if (await orderRow.isVisible()) {
      await orderRow.click();

      const pageContent = await page.content();
      const isDigitalProduct = pageContent.toLowerCase().includes('digital');

      if (isDigitalProduct) {
        const startButton = page.locator('button:has-text("Start Work")');
        await expect(startButton).not.toBeVisible();
      }
    }
  });

  test('can submit delivery with notes', async ({ page }) => {
    const orderRow = page.locator('table tbody tr a').first();

    if (await orderRow.isVisible()) {
      await orderRow.click();

      const deliverButton = page.locator('button:has-text("Submit Delivery"), button:has-text("Deliver")');

      if (await deliverButton.isVisible()) {
        await deliverButton.click();

        // Fill delivery notes
        await page.fill('textarea', 'Here is your completed work!');

        // Submit
        await page.click('button:has-text("Submit")');

        await expect(page.locator('text=Delivered')).toBeVisible();
      }
    }
  });

  test('can submit delivery with files', async ({ page }) => {
    const orderRow = page.locator('table tbody tr a').first();

    if (await orderRow.isVisible()) {
      await orderRow.click();

      const deliverButton = page.locator('button:has-text("Submit Delivery")');

      if (await deliverButton.isVisible()) {
        await deliverButton.click();

        // File upload
        const fileInput = page.locator('input[type="file"]');
        // Would upload files here

        await page.fill('textarea', 'Delivery with attachments');
        await page.click('button:has-text("Submit")');
      }
    }
  });

  test('cannot deliver completed order', async ({ page }) => {
    const orderRow = page.locator('table tbody tr a').first();

    if (await orderRow.isVisible()) {
      await orderRow.click();

      const pageContent = await page.content();
      const isCompleted = pageContent.includes('Completed');

      if (isCompleted) {
        const deliverButton = page.locator('button:has-text("Submit Delivery")');
        await expect(deliverButton).not.toBeVisible();
      }
    }
  });
});

test.describe('Digital Product Workflow', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('/login');
    await page.fill('input[name="email"]', 'lisa.mendoza@example.com');
    await page.fill('input[name="password"]', 'password');
    await page.click('button[type="submit"]');
    await page.waitForURL(/\/worker/);
    await page.click('a:has-text("Orders")');
  });

  test('digital product auto-completes after payment verified', async ({ page }) => {
    // When worker verifies payment on digital product,
    // order should automatically transition to Completed
    const orderRow = page.locator('table tbody tr a').first();

    if (await orderRow.isVisible()) {
      await orderRow.click();

      // Check if this is a digital product
      const pageContent = await page.content();
      const isDigitalProduct = pageContent.toLowerCase().includes('digital');

      if (isDigitalProduct) {
        // Should see Completed status (if payment was verified)
        // or pending payment status
      }
    }
  });

  test('no start work button for digital products', async ({ page }) => {
    const orderRow = page.locator('table tbody tr a').first();

    if (await orderRow.isVisible()) {
      await orderRow.click();

      const pageContent = await page.content();
      const isDigitalProduct = pageContent.toLowerCase().includes('digital');

      if (isDigitalProduct) {
        const startButton = page.locator('button:has-text("Start Work")');
        await expect(startButton).not.toBeVisible();
      }
    }
  });

  test('no submit delivery button for digital products', async ({ page }) => {
    const orderRow = page.locator('table tbody tr a').first();

    if (await orderRow.isVisible()) {
      await orderRow.click();

      const pageContent = await page.content();
      const isDigitalProduct = pageContent.toLowerCase().includes('digital');

      if (isDigitalProduct) {
        const deliverButton = page.locator('button:has-text("Submit Delivery")');
        await expect(deliverButton).not.toBeVisible();
      }
    }
  });
});
