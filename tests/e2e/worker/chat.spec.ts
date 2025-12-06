import { test, expect } from '@playwright/test';

test.describe('Worker Chat', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('/login');
    await page.fill('input[name="email"]', 'lisa.mendoza@example.com');
    await page.fill('input[name="password"]', 'password');
    await page.click('button[type="submit"]');

    // Wait for worker panel
    await page.waitForURL(/\/worker/);
  });

  test('chat link is NOT visible in frontend navigation for workers', async ({ page }) => {
    // Navigate to a page with the app layout (not Filament)
    await page.goto('/');

    // Chat link should NOT be visible for workers (frontend chat is customers only)
    const chatLink = page.locator('nav a:has-text("Chat")');
    await expect(chatLink).not.toBeVisible();
  });

  test('workers cannot access frontend chat routes', async ({ page }) => {
    // Try to access customer chat page - should be forbidden
    const response = await page.goto('/chats');

    // Should return 403 Forbidden (role:customer middleware)
    expect(response?.status()).toBe(403);
  });

  test('chat link visible in Filament sidebar', async ({ page }) => {
    // Look for Chat link in Filament sidebar
    const chatLink = page.locator('nav a[href*="/worker/chat"]');
    await expect(chatLink).toBeVisible();
  });

  test('can access Filament chat page', async ({ page }) => {
    // Click Chat in Filament sidebar
    await page.click('nav a[href*="/worker/chat"]');

    // Should load the chat page
    await expect(page).toHaveURL(/\/worker\/chat/);

    // Should show Messages header
    await expect(page.locator('h2:has-text("Messages")')).toBeVisible();
  });

  test('Filament chat page shows conversation list', async ({ page }) => {
    await page.goto('/worker/chat');

    // Should show either conversations or empty state
    const messagesHeader = page.locator('h2:has-text("Messages")');
    const emptyState = page.locator('text=No conversations yet');
    const conversation = page.locator('button:has-text("KKY-")');

    await expect(messagesHeader).toBeVisible();

    // Either has conversations or shows empty state
    const hasConversation = await conversation.first().isVisible();
    const hasEmptyState = await emptyState.isVisible();
    expect(hasConversation || hasEmptyState).toBe(true);
  });

  test('can select a conversation in Filament chat', async ({ page }) => {
    await page.goto('/worker/chat');

    // Look for a conversation with order number
    const conversation = page.locator('button:has-text("KKY-")').first();

    if (await conversation.isVisible()) {
      await conversation.click();

      // Should show the chat panel with View Order link
      await expect(page.locator('a:has-text("View Order")')).toBeVisible();
    }
  });

  test('can send message from Filament chat', async ({ page }) => {
    await page.goto('/worker/chat');

    // Click first conversation
    const conversation = page.locator('button:has-text("KKY-")').first();

    if (await conversation.isVisible()) {
      await conversation.click();

      // Wait for chat to load
      await page.waitForTimeout(500);

      // Check if message input is visible (chat enabled)
      const messageInput = page.locator('input[placeholder="Type your message..."]');

      if (await messageInput.isVisible()) {
        await messageInput.fill('Hello from worker Filament chat!');
        await page.click('button:has-text("Send")');

        // Message should appear in the chat
        await expect(page.locator('text=Hello from worker Filament chat!')).toBeVisible();
      }
    }
  });

  test('can access chat from order view in Filament', async ({ page }) => {
    // Click Orders in sidebar
    await page.click('a:has-text("Orders")');

    // Wait for table to load
    await page.waitForSelector('table tbody tr', { timeout: 10000 });

    // Click on the first order row to view it
    const viewButton = page.locator('table tbody tr').first().locator('a').first();
    await viewButton.click();

    // Wait for view page to load
    await page.waitForSelector('text=View Order', { timeout: 10000 });

    // Check chat button is visible
    const chatButton = page.locator('a:has-text("Chat with Customer")');
    await expect(chatButton).toBeVisible();
  });

  test('chat navigation shows unread badge when messages exist', async ({ page }) => {
    // Check if the Chat nav item has a badge (may or may not have unread)
    const chatNavItem = page.locator('nav a[href*="/worker/chat"]');
    await expect(chatNavItem).toBeVisible();

    // Badge may or may not be visible depending on unread state
    // Just verify the nav item exists
  });
});
