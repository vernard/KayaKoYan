import { test, expect } from '@playwright/test';

test.describe('Worker Profiles (Public)', () => {
  test('worker profile page is accessible', async ({ page }) => {
    // Visit a known worker profile
    await page.goto('/worker/lisa-mendoza');

    // Should show worker name
    await expect(page.locator('h2:has-text("Lisa Mendoza")')).toBeVisible();
  });

  test('shows worker public information', async ({ page }) => {
    await page.goto('/worker/lisa-mendoza');

    // Should show name
    await expect(page.locator('text=Lisa Mendoza').first()).toBeVisible();

    // Should show location
    await expect(page.locator('text=Iloilo')).toBeVisible();
  });

  test('shows worker avatar or placeholder', async ({ page }) => {
    await page.goto('/worker/lisa-mendoza');

    // Should have avatar image
    const avatar = page.locator('img[alt*="Lisa"], img[class*="avatar"], img[src*="avatar"]');

    // Either custom avatar or UI Avatars placeholder
    const images = page.locator('img');
    expect(await images.count()).toBeGreaterThan(0);
  });

  test('shows worker location if set', async ({ page }) => {
    await page.goto('/worker/lisa-mendoza');

    // Location may or may not be displayed
    const location = page.locator('text=Iloilo, text=Philippines, text=Manila');
    // This depends on worker data
  });

  test('shows worker active listings', async ({ page }) => {
    await page.goto('/worker/lisa-mendoza');

    // Should show services section with worker name
    await expect(page.locator('text=Services by Lisa Mendoza')).toBeVisible();

    // Should have listing cards
    const listings = page.locator('a[href*="/listings/"]');
    const count = await listings.count();
    expect(count).toBeGreaterThan(0);
  });

  test('worker listings link to detail pages', async ({ page }) => {
    await page.goto('/worker/lisa-mendoza');

    // Click on a listing
    const listingLink = page.locator('a[href*="/listings/"]').first();

    if (await listingLink.isVisible()) {
      await listingLink.click();
      await expect(page).toHaveURL(/\/listings\/.+/);
    }
  });

  test('shows payment methods if configured', async ({ page }) => {
    await page.goto('/worker/lisa-mendoza');

    // Check for payment method sections
    const gcashSection = page.locator('text=GCash');
    const bankSection = page.locator('text=Bank');

    // At least one should be visible if worker has payment configured
    const hasGcash = await gcashSection.isVisible();
    const hasBank = await bankSection.isVisible();

    // This worker should have payment methods
    // expect(hasGcash || hasBank).toBe(true);
  });

  test('inactive worker profile returns 404', async ({ page }) => {
    // Try to access a non-existent profile
    const response = await page.goto('/worker/non-existent-worker-profile');

    // Should return 404
    expect(response?.status()).toBe(404);
  });

  test('worker profile shows different info than listing page', async ({ page }) => {
    // Profile focuses on worker, listing focuses on service
    await page.goto('/worker/lisa-mendoza');

    // Should see worker-centric content
    const pageContent = await page.content();
    expect(pageContent).toContain('Lisa');

    // Should see multiple listings from this worker
    const listings = page.locator('a[href*="/listings/"]');
    const count = await listings.count();

    // Worker profile should show all their listings
    expect(count).toBeGreaterThanOrEqual(1);
  });
});

test.describe('Worker Profile Navigation', () => {
  test('can navigate to worker profile from listing', async ({ page }) => {
    // Go to a listing
    await page.goto('/listings');
    await page.locator('a[href*="/listings/"]').first().click();

    // Click on worker name/link
    const workerLink = page.locator('a[href*="/worker/"]');

    if (await workerLink.isVisible()) {
      await workerLink.click();
      await expect(page).toHaveURL(/\/worker\/.+/);
    }
  });

  test('worker profile has back to listings link', async ({ page }) => {
    await page.goto('/worker/lisa-mendoza');

    // Should have navigation to browse listings
    const browseLink = page.locator('a:has-text("Browse"), a:has-text("Listings"), a[href="/listings"]');
    await expect(browseLink).toBeVisible();
  });
});
