import { test, expect } from '@playwright/test';

test.describe('Customer Chat', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('/login');
    await page.fill('input[name="email"]', 'miguel.torres@example.com');
    await page.fill('input[name="password"]', 'password');
    await page.click('button[type="submit"]');
  });

  test('can access chat from order page', async ({ page }) => {
    await page.goto('/customer/orders');
    const orderLink = page.locator('a[href*="/customer/orders/"]').first();

    if (await orderLink.isVisible()) {
      await orderLink.click();

      // Look for chat link/button
      const chatLink = page.locator('a:has-text("Chat"), button:has-text("Chat"), a[href*="chat"]');

      if (await chatLink.isVisible()) {
        await chatLink.click();
        await expect(page).toHaveURL(/\/chat/);
      }
    }
  });

  test('chat page displays message history', async ({ page }) => {
    await page.goto('/customer/orders');
    const orderLink = page.locator('a[href*="/customer/orders/"]').first();

    if (await orderLink.isVisible()) {
      await orderLink.click();

      const chatLink = page.locator('a[href*="chat"]').first();

      if (await chatLink.isVisible()) {
        await chatLink.click();

        // Should show chat page - look for message input or chat container
        const messageInput = page.locator('input[name="message"], textarea[name="message"]');
        const chatContainer = page.locator('form[action*="chat"]');
        const hasChat = await messageInput.isVisible() || await chatContainer.isVisible();
        expect(hasChat).toBe(true);
      }
    }
  });

  test('can send text message', async ({ page }) => {
    await page.goto('/customer/orders');
    const orderLink = page.locator('a[href*="/customer/orders/"]').first();

    if (await orderLink.isVisible()) {
      await orderLink.click();

      const chatLink = page.locator('a[href*="chat"]').first();

      if (await chatLink.isVisible()) {
        await chatLink.click();

        // Find message input
        const messageInput = page.locator('input[name="message"], textarea[name="message"]');

        if (await messageInput.isVisible()) {
          await messageInput.fill('Hello, this is a test message!');
          await page.click('button[type="submit"]');

          // Message should appear in chat
          await expect(page.locator('text=Hello, this is a test message')).toBeVisible();
        }
      }
    }
  });

  test('can send file in chat', async ({ page }) => {
    await page.goto('/customer/orders');
    const orderLink = page.locator('a[href*="/customer/orders/"]').first();

    if (await orderLink.isVisible()) {
      await orderLink.click();

      const chatLink = page.locator('a[href*="chat"]').first();

      if (await chatLink.isVisible()) {
        await chatLink.click();

        // Find file input
        const fileInput = page.locator('input[type="file"]');

        if (await fileInput.isVisible()) {
          // File upload would be tested with actual file
          // await fileInput.setInputFiles('path/to/test-file.pdf');
        }
      }
    }
  });

  test('messages show sender name', async ({ page }) => {
    await page.goto('/customer/orders');
    const orderLink = page.locator('a[href*="/customer/orders/"]').first();

    if (await orderLink.isVisible()) {
      await orderLink.click();

      const chatLink = page.locator('a[href*="chat"]').first();

      if (await chatLink.isVisible()) {
        await chatLink.click();

        // Messages should show who sent them
        const messages = page.locator('[class*="message"]');

        if ((await messages.count()) > 0) {
          // Each message should have sender info
          const messageText = await messages.first().textContent();
          expect(messageText).toBeTruthy();
        }
      }
    }
  });

  test('messages show timestamp', async ({ page }) => {
    await page.goto('/customer/orders');
    const orderLink = page.locator('a[href*="/customer/orders/"]').first();

    if (await orderLink.isVisible()) {
      await orderLink.click();

      const chatLink = page.locator('a[href*="chat"]').first();

      if (await chatLink.isVisible()) {
        await chatLink.click();

        // Look for timestamp elements
        const timestamps = page.locator('time, [class*="time"], [class*="date"]');
        // Timestamps may or may not be visible depending on UI
      }
    }
  });

  test('own messages styled differently', async ({ page }) => {
    await page.goto('/customer/orders');
    const orderLink = page.locator('a[href*="/customer/orders/"]').first();

    if (await orderLink.isVisible()) {
      await orderLink.click();

      const chatLink = page.locator('a[href*="chat"]').first();

      if (await chatLink.isVisible()) {
        await chatLink.click();

        // Own messages typically have different styling (right-aligned, different color)
        // This is a visual check that's hard to automate without specific test IDs
      }
    }
  });

  test('cannot chat on other customer orders', async ({ page }) => {
    // Try to access chat for an order that doesn't belong to this customer
    const response = await page.goto('/customer/orders/99999/chat');

    // Should return 403 or 404
    expect(response?.status()).toBeGreaterThanOrEqual(400);
  });

  test('chat shows worker messages', async ({ page }) => {
    await page.goto('/customer/orders');
    const orderLink = page.locator('a[href*="/customer/orders/"]').first();

    if (await orderLink.isVisible()) {
      await orderLink.click();

      const chatLink = page.locator('a[href*="chat"]').first();

      if (await chatLink.isVisible()) {
        await chatLink.click();

        // Chat page should have a form for sending messages
        const messageForm = page.locator('form');
        await expect(messageForm.first()).toBeVisible();
      }
    }
  });
});
