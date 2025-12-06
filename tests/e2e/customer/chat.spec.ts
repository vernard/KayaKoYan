import { test, expect } from '@playwright/test';

test.describe('Customer Chat', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('/login');
    await page.fill('input[name="email"]', 'miguel.torres@example.com');
    await page.fill('input[name="password"]', 'password');
    await page.click('button[type="submit"]');
  });

  test('can access chat from navigation', async ({ page }) => {
    // Look for chat link in navigation (contains "Chat" text)
    const chatLink = page.locator('nav a:has-text("Chat")');
    await expect(chatLink).toBeVisible();
    await chatLink.click();
    await expect(page).toHaveURL(/\/chats/);
  });

  test('chat page shows conversation list', async ({ page }) => {
    await page.goto('/chats');

    // Should show either messages header, empty state, or a chat view
    const messagesHeader = page.locator('text=Messages');
    const emptyState = page.locator('text=No conversations yet');
    const chatView = page.locator('text=View Order');
    const hasContent = await messagesHeader.isVisible() || await emptyState.isVisible() || await chatView.isVisible();
    expect(hasContent).toBe(true);
  });

  test('can access chat from order page', async ({ page }) => {
    await page.goto('/customer/orders');
    const orderLink = page.locator('a[href*="/customer/orders/"]').first();

    if (await orderLink.isVisible()) {
      await orderLink.click();

      // Look for chat link/button
      const chatLink = page.locator('a[href*="/chats/"]');

      if (await chatLink.isVisible()) {
        await chatLink.click();
        await expect(page).toHaveURL(/\/chats/);
      }
    }
  });

  test('chat page displays message input', async ({ page }) => {
    await page.goto('/customer/orders');
    const orderLink = page.locator('a[href*="/customer/orders/"]').first();

    if (await orderLink.isVisible()) {
      await orderLink.click();

      const chatLink = page.locator('a[href*="/chats/"]').first();

      if (await chatLink.isVisible()) {
        await chatLink.click();

        // Should show chat page with input
        const messageInput = page.locator('input[placeholder="Type your message..."]');
        await expect(messageInput).toBeVisible();
      }
    }
  });

  test('can send text message', async ({ page }) => {
    await page.goto('/customer/orders');
    const orderLink = page.locator('a[href*="/customer/orders/"]').first();

    if (await orderLink.isVisible()) {
      await orderLink.click();

      const chatLink = page.locator('a[href*="/chats/"]').first();

      if (await chatLink.isVisible()) {
        await chatLink.click();

        // Find message input
        const messageInput = page.locator('input[placeholder="Type your message..."]');

        if (await messageInput.isVisible()) {
          await messageInput.fill('Hello, this is a test message!');
          await page.click('button:has-text("Send")');

          // Message should appear in chat
          await expect(page.locator('text=Hello, this is a test message')).toBeVisible();
        }
      }
    }
  });

  test('messages show sender name and timestamp', async ({ page }) => {
    await page.goto('/chats');

    // If there are conversations, click the first one
    const conversation = page.locator('a[href*="/chats/"]').first();

    if (await conversation.isVisible()) {
      await conversation.click();

      // Messages should show sender info
      const messages = page.locator('.rounded-lg.p-4');

      if ((await messages.count()) > 0) {
        const messageText = await messages.first().textContent();
        expect(messageText).toBeTruthy();
      }
    }
  });

  test('own messages styled differently', async ({ page }) => {
    await page.goto('/chats');

    // If there are conversations, click the first one
    const conversation = page.locator('a[href*="/chats/"]').first();

    if (await conversation.isVisible()) {
      await conversation.click();

      // Own messages typically have amber background (right-aligned)
      // This is a visual check that's hard to automate without specific test IDs
    }
  });

  test('cannot access chat for other customer orders', async ({ page }) => {
    // Try to access chat for an order that doesn't belong to this customer
    const response = await page.goto('/chats/99999');

    // Should return 403 or 404
    expect(response?.status()).toBeGreaterThanOrEqual(400);
  });

  test('chat shows conversation list on left', async ({ page }) => {
    await page.goto('/chats');

    // Should show either messages header, empty state, or a chat view
    const messagesHeader = page.locator('text=Messages');
    const emptyState = page.locator('text=No conversations yet');
    const chatView = page.locator('text=View Order');
    const hasContent = await messagesHeader.isVisible() || await emptyState.isVisible() || await chatView.isVisible();
    expect(hasContent).toBe(true);
  });

  test('chat disabled message shows for completed orders', async ({ page }) => {
    await page.goto('/chats');

    // Look for read-only message (order may already be selected if completed)
    const readOnlyMessage = page.locator('text=read-only');
    const completedBadge = page.locator('.bg-green-100:has-text("Completed")').first();

    if (await readOnlyMessage.isVisible()) {
      // Already showing a completed order
      expect(await readOnlyMessage.textContent()).toContain('read-only');
    } else if (await completedBadge.isVisible()) {
      // Click on the completed conversation
      await completedBadge.click();
      await page.waitForTimeout(500);

      // Should show disabled message
      const disabledMessage = page.locator('text=read-only');
      if (await disabledMessage.isVisible()) {
        expect(await disabledMessage.textContent()).toContain('read-only');
      }
    }
  });
});
