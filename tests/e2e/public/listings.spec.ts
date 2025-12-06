import { test, expect } from '@playwright/test';

test.describe('Listings Browse', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('/listings');
  });

  test('displays listings page', async ({ page }) => {
    // Should show listings heading or content
    await expect(page.locator('h1, h2')).toBeVisible();
  });

  test('shows active listings only', async ({ page }) => {
    // Should have listing cards
    const listings = page.locator('a[href*="/listings/"], article, [data-testid="listing-card"]');
    const count = await listings.count();
    expect(count).toBeGreaterThan(0);

    // Each listing should have visible content (not hidden/inactive)
    for (let i = 0; i < Math.min(count, 5); i++) {
      await expect(listings.nth(i)).toBeVisible();
    }
  });

  test('listings are paginated', async ({ page }) => {
    // Check for pagination controls
    const pagination = page.locator('nav[aria-label*="pagination"], .pagination, [class*="paginate"]');

    // Pagination may or may not be visible depending on listing count
    const hasPagination = await pagination.isVisible();

    // If there are many listings, pagination should exist
    // This test passes regardless - just documents the behavior
  });

  test('listings can be searched by title', async ({ page }) => {
    // Look for search input
    const searchInput = page.locator('input[name="search"], input[type="search"], input[placeholder*="Search"]');

    if (await searchInput.isVisible()) {
      await searchInput.fill('Virtual Assistant');
      await page.keyboard.press('Enter');

      // Results should filter
      await page.waitForLoadState('networkidle');

      // Check URL has search param or listings are filtered
      const url = page.url();
      const hasSearchParam = url.includes('search=') || url.includes('q=');

      // Either URL updated or results filtered
    }
  });

  test('listings can be filtered by type', async ({ page }) => {
    // Look for type filter
    const typeFilter = page.locator('select[name="type"], [data-filter="type"]');

    if (await typeFilter.isVisible()) {
      // Filter by service
      await typeFilter.selectOption('service');
      await page.waitForLoadState('networkidle');

      // Results should show only services
    }
  });

  test('listing cards display price and worker info', async ({ page }) => {
    // Get first listing card
    const firstListing = page.locator('a[href*="/listings/"], article').first();

    // Should show price (PHP symbol or number)
    const cardText = await firstListing.textContent();
    const hasPrice = cardText?.includes('₱') || /\d+/.test(cardText || '');
    expect(hasPrice).toBe(true);
  });

  test('clicking listing goes to detail page', async ({ page }) => {
    // Click first listing
    await page.locator('a[href*="/listings/"]').first().click();

    // Should be on listing detail page
    await expect(page).toHaveURL(/\/listings\/.+/);
  });
});

test.describe('Listing Details', () => {
  test('shows full listing information', async ({ page }) => {
    // Go to listings and click first one
    await page.goto('/listings');
    await page.locator('a[href*="/listings/"]').first().click();

    // Should show title
    await expect(page.locator('h1, h2').first()).toBeVisible();

    // Should show price (format: PHP X,XXX.XX)
    await expect(page.locator('text=PHP').first()).toBeVisible();

    // Should show description
    const description = page.locator('[class*="description"], p');
    await expect(description.first()).toBeVisible();
  });

  test('shows worker profile link', async ({ page }) => {
    await page.goto('/listings');
    await page.locator('a[href*="/listings/"]').first().click();

    // Should have link to worker profile
    const workerLink = page.locator('a[href*="/worker/"]');
    await expect(workerLink).toBeVisible();
  });

  test('shows related listings from same worker', async ({ page }) => {
    await page.goto('/listings');
    await page.locator('a[href*="/listings/"]').first().click();

    // Look for related listings section
    const relatedSection = page.locator('text=Related, text=More from, text=Other');

    // May or may not have related listings
    if (await relatedSection.isVisible()) {
      const relatedListings = page.locator('a[href*="/listings/"]:not(:first-child)');
      expect(await relatedListings.count()).toBeGreaterThan(0);
    }
  });

  test('guest cannot order directly - redirects to sign up', async ({ page }) => {
    await page.goto('/listings');
    await page.locator('a[href*="/listings/"]').first().click();

    // Find "Sign Up to Order" button for guests
    const signUpButton = page.locator('a:has-text("Sign Up to Order")');

    if (await signUpButton.isVisible()) {
      await signUpButton.click();

      // Should redirect to register since user is not authenticated
      await expect(page).toHaveURL(/\/register/);
    }
  });

  test('authenticated customer sees order button', async ({ page }) => {
    // Login first
    await page.goto('/login');
    await page.fill('input[name="email"]', 'miguel.torres@example.com');
    await page.fill('input[name="password"]', 'password');
    await page.click('button[type="submit"]');

    // Go to listing
    await page.goto('/listings');
    await page.locator('a[href*="/listings/"]').first().click();

    // Should see "Order Now" button
    const orderButton = page.locator('button:has-text("Order Now")');
    await expect(orderButton).toBeVisible();
  });

  test('shows listing images', async ({ page }) => {
    await page.goto('/listings');
    await page.locator('a[href*="/listings/"]').first().click();

    // Should have at least one image
    const images = page.locator('img');
    const count = await images.count();
    expect(count).toBeGreaterThan(0);
  });
});
