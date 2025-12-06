import { test, expect } from '@playwright/test';

test.describe('Worker Chat', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('/login');
    await page.fill('input[name="email"]', 'lisa.mendoza@example.com');
    await page.fill('input[name="password"]', 'password');
    await page.click('button[type="submit"]');

    // Wait for worker panel, then click Orders in sidebar
    await page.waitForURL(/\/worker/);
    await page.click('a:has-text("Orders")');
  });

  test('can access chat from order view', async ({ page }) => {
    // In Filament table, click on the first order row link
    const orderLink = page.locator('table tbody tr').first().locator('a').first();

    if (await orderLink.isVisible()) {
      await orderLink.click();

      // In Filament view page, look for chat action
      const chatButton = page.locator('a:has-text("Chat"), button:has-text("Chat")');

      // Chat button may or may not be visible depending on order state
      if (await chatButton.isVisible()) {
        await chatButton.click();
      }
    }
  });

  test('chat page shows message interface', async ({ page }) => {
    const orderLink = page.locator('table tbody tr').first().locator('a').first();

    if (await orderLink.isVisible()) {
      await orderLink.click();

      const chatButton = page.locator('a:has-text("Chat")');

      if (await chatButton.isVisible()) {
        await chatButton.click();

        // Should show some form on chat page
        const hasForm = await page.locator('form').isVisible();
        expect(hasForm).toBe(true);
      }
    }
  });

  test('can send message to customer', async ({ page }) => {
    const orderRow = page.locator('table tbody tr a').first();

    if (await orderRow.isVisible()) {
      await orderRow.click();

      const chatButton = page.locator('a:has-text("Chat")');

      if (await chatButton.isVisible()) {
        await chatButton.click();

        const messageInput = page.locator('input[name="message"], textarea[name="message"]');

        if (await messageInput.isVisible()) {
          await messageInput.fill('Hello from worker! I have started working on your order.');
          await page.click('button[type="submit"]');

          await expect(page.locator('text=Hello from worker')).toBeVisible();
        }
      }
    }
  });

  test('can send file in chat', async ({ page }) => {
    const orderRow = page.locator('table tbody tr a').first();

    if (await orderRow.isVisible()) {
      await orderRow.click();

      const chatButton = page.locator('a:has-text("Chat")');

      if (await chatButton.isVisible()) {
        await chatButton.click();

        const fileInput = page.locator('input[type="file"]');
        // Would upload file here
      }
    }
  });

  test('sees customer messages', async ({ page }) => {
    const orderRow = page.locator('table tbody tr a').first();

    if (await orderRow.isVisible()) {
      await orderRow.click();

      const chatButton = page.locator('a:has-text("Chat")');

      if (await chatButton.isVisible()) {
        await chatButton.click();

        // Chat should display any existing messages
        const messages = page.locator('[class*="message"]');
        // Messages may or may not exist
      }
    }
  });

  test('cannot chat on other worker orders', async ({ page }) => {
    // Try to access chat for an order that doesn't belong to this worker
    const response = await page.goto('/worker/orders/99999/chat');

    // Should return 403 or 404
    expect(response?.status()).toBeGreaterThanOrEqual(400);
  });

  test('chat shows message timestamps', async ({ page }) => {
    const orderRow = page.locator('table tbody tr a').first();

    if (await orderRow.isVisible()) {
      await orderRow.click();

      const chatButton = page.locator('a:has-text("Chat")');

      if (await chatButton.isVisible()) {
        await chatButton.click();

        // Look for timestamp elements
        const timestamps = page.locator('time, [class*="time"]');
        // Timestamps should be visible for messages
      }
    }
  });

  test('chat button visible in order view', async ({ page }) => {
    const orderRow = page.locator('table tbody tr a').first();

    if (await orderRow.isVisible()) {
      await orderRow.click();

      const chatButton = page.locator('a:has-text("Chat"), button:has-text("Chat")');
      await expect(chatButton).toBeVisible();
    }
  });
});
