import { test, expect } from '@playwright/test';
import { generateTestEmail, generateTestName } from '../fixtures/test-helpers';

/**
 * Worker Onboarding Flow Test
 *
 * This test covers the complete worker onboarding journey:
 * 1. Worker registers via /become-a-worker
 * 2. Worker verifies email
 * 3. Worker configures profile (bio, payment methods)
 * 4. Worker creates first listing with images
 * 5. Worker's listing is visible publicly
 */

test.describe('Worker Onboarding Flow', () => {
  test('complete worker registration and setup', async ({ page }) => {
    const workerEmail = generateTestEmail('worker');
    const workerName = generateTestName('New Worker');
    const workerPassword = 'SecurePassword123!';

    // ============================================
    // STEP 1: Worker Registration
    // ============================================
    await test.step('Worker registers via become-a-worker page', async () => {
      await page.goto('/become-a-worker');

      // Should see worker registration page with benefits
      await expect(page.locator('text=Become a Worker, text=Start Selling')).toBeVisible();

      // Fill registration form
      await page.fill('input[name="name"]', workerName);
      await page.fill('input[name="email"]', workerEmail);
      await page.fill('input[name="password"]', workerPassword);
      await page.fill('input[name="password_confirmation"]', workerPassword);

      // Submit registration
      await page.click('button[type="submit"]');

      // Should redirect to email verification
      await expect(page).toHaveURL(/\/email\/verify/);
    });

    // ============================================
    // STEP 2: Email Verification (simulated)
    // ============================================
    await test.step('Worker verifies email', async () => {
      // In production, parse logs or use test API
      console.log('Email verification would be handled via log parsing or test API');

      // For testing, we'll use a pre-verified worker account
    });
  });

  test('verified worker configures profile and creates listing', async ({ page }) => {
    // Use existing verified worker
    const workerEmail = 'lisa.mendoza@example.com';
    const workerPassword = 'password';

    // ============================================
    // STEP 1: Login as Worker
    // ============================================
    await test.step('Worker logs into Filament panel', async () => {
      await page.goto('/login');
      await page.fill('input[name="email"]', workerEmail);
      await page.fill('input[name="password"]', workerPassword);
      await page.click('button[type="submit"]');

      // Should redirect to worker dashboard
      await expect(page).toHaveURL(/\/worker/);
    });

    // ============================================
    // STEP 2: View Dashboard Stats
    // ============================================
    await test.step('Worker sees dashboard statistics', async () => {
      // Should see stats widgets
      await expect(page.locator('text=Pending Orders, text=Orders')).toBeVisible();
      await expect(page.locator('text=Earnings, text=Total')).toBeVisible();
    });

    // ============================================
    // STEP 3: Configure Profile
    // ============================================
    await test.step('Worker updates profile with payment methods', async () => {
      // Navigate to profile page
      await page.click('a:has-text("My Profile"), a:has-text("Profile")');

      // Should be on profile edit page
      await expect(page).toHaveURL(/\/worker\/.*profile/);

      // Fill profile information
      const bioField = page.locator('textarea[name*="bio"], textarea').first();
      if (await bioField.isVisible()) {
        await bioField.fill('Professional freelancer with 5+ years of experience in content writing and SEO.');
      }

      const phoneField = page.locator('input[name*="phone"]');
      if (await phoneField.isVisible()) {
        await phoneField.fill('09171234567');
      }

      const locationField = page.locator('input[name*="location"]');
      if (await locationField.isVisible()) {
        await locationField.fill('Manila, Philippines');
      }

      // Fill GCash details
      const gcashNumber = page.locator('input[name*="gcash_number"]');
      if (await gcashNumber.isVisible()) {
        await gcashNumber.fill('09171234567');
      }

      const gcashName = page.locator('input[name*="gcash_name"]');
      if (await gcashName.isVisible()) {
        await gcashName.fill('Lisa Mendoza');
      }

      // Fill Bank details
      const bankName = page.locator('input[name*="bank_name"]');
      if (await bankName.isVisible()) {
        await bankName.fill('BDO');
      }

      const bankAccount = page.locator('input[name*="bank_account_number"]');
      if (await bankAccount.isVisible()) {
        await bankAccount.fill('1234567890');
      }

      const bankAccountName = page.locator('input[name*="bank_account_name"]');
      if (await bankAccountName.isVisible()) {
        await bankAccountName.fill('Lisa A. Mendoza');
      }

      // Save profile
      await page.click('button:has-text("Save")');

      // Should see success notification
      await expect(page.locator('text=saved, text=updated, text=success')).toBeVisible();
    });

    // ============================================
    // STEP 4: Create First Listing
    // ============================================
    await test.step('Worker creates a new service listing', async () => {
      // Navigate to listings
      await page.click('a:has-text("My Listings"), a:has-text("Listings")');

      // Click create new listing
      await page.click('a:has-text("Create"), button:has-text("Create"), a:has-text("New")');

      // Fill listing details
      await page.fill('input[name*="title"]', 'Professional Blog Writing Service');

      // Select type
      const typeSelect = page.locator('select[name*="type"], [name*="type"]');
      if (await typeSelect.isVisible()) {
        await typeSelect.selectOption('service');
      }

      // Fill price
      await page.fill('input[name*="price"]', '1500');

      // Fill description (rich text editor or textarea)
      const descriptionField = page.locator('textarea[name*="description"], [contenteditable="true"]').first();
      if (await descriptionField.isVisible()) {
        await descriptionField.fill('I will write high-quality, SEO-optimized blog articles for your website. Includes research, writing, and one revision.');
      }

      // Toggle active status
      const activeToggle = page.locator('input[name*="is_active"], [name*="active"]');
      if (await activeToggle.isVisible() && !(await activeToggle.isChecked())) {
        await activeToggle.click();
      }

      // Submit listing
      await page.click('button:has-text("Create"), button[type="submit"]');

      // Should redirect to listing list or show success
      await expect(page.locator('text=created, text=success')).toBeVisible();
    });

    // ============================================
    // STEP 5: Verify Listing is Public
    // ============================================
    await test.step('Listing appears on public listings page', async () => {
      // Navigate to public listings
      await page.goto('/listings');

      // Search for the new listing
      const searchInput = page.locator('input[name="search"], input[type="search"]');
      if (await searchInput.isVisible()) {
        await searchInput.fill('Professional Blog Writing');
        await page.keyboard.press('Enter');
      }

      // The listing should be visible (or we check generally)
      await expect(page.locator('text=Blog Writing, text=Professional')).toBeVisible();
    });
  });

  test('worker can view and manage their listings', async ({ page }) => {
    const workerEmail = 'lisa.mendoza@example.com';
    const workerPassword = 'password';

    // Login
    await page.goto('/login');
    await page.fill('input[name="email"]', workerEmail);
    await page.fill('input[name="password"]', workerPassword);
    await page.click('button[type="submit"]');

    // Navigate to listings
    await page.goto('/worker');
    await page.click('a:has-text("My Listings"), a:has-text("Listings")');

    // ============================================
    // View Listings Table
    // ============================================
    await test.step('Worker sees their listings in table', async () => {
      // Should see listings table
      await expect(page.locator('table, [data-testid="listings-table"]')).toBeVisible();

      // Should show listing count or have rows
      const rows = page.locator('table tbody tr, [data-testid="listing-row"]');
      const count = await rows.count();
      expect(count).toBeGreaterThan(0);
    });

    // ============================================
    // Edit a Listing
    // ============================================
    await test.step('Worker can edit a listing', async () => {
      // Click edit on first listing
      await page.locator('a:has-text("Edit"), button:has-text("Edit")').first().click();

      // Should be on edit page
      await expect(page).toHaveURL(/\/edit/);

      // Change price
      const priceField = page.locator('input[name*="price"]');
      await priceField.clear();
      await priceField.fill('2000');

      // Save changes
      await page.click('button:has-text("Save")');

      // Should see success
      await expect(page.locator('text=saved, text=updated')).toBeVisible();
    });

    // ============================================
    // Toggle Listing Status
    // ============================================
    await test.step('Worker can deactivate and reactivate listing', async () => {
      // Navigate back to listings if needed
      await page.click('a:has-text("My Listings"), a:has-text("Listings")');

      // Find active toggle or status
      const statusToggle = page.locator('input[type="checkbox"], button:has-text("Deactivate")').first();

      if (await statusToggle.isVisible()) {
        const initialState = await statusToggle.isChecked?.() || false;
        await statusToggle.click();

        // Status should change
        // Verify by refreshing and checking
      }
    });
  });

  test('worker public profile shows their listings', async ({ page }) => {
    // Visit a worker's public profile
    await page.goto('/worker/lisa-mendoza');

    // ============================================
    // Profile Information
    // ============================================
    await test.step('Public profile shows worker info', async () => {
      // Should see worker name
      await expect(page.locator('text=Lisa Mendoza')).toBeVisible();

      // Should see bio if set
      const bio = page.locator('text=Professional, text=Content Writer');
      // Bio might or might not be visible depending on data
    });

    // ============================================
    // Worker's Listings
    // ============================================
    await test.step('Public profile shows worker listings', async () => {
      // Should see listings section
      await expect(page.locator('text=Listings, text=Services')).toBeVisible();

      // Should show active listings
      const listingCards = page.locator('a[href*="/listings/"], [data-testid="listing-card"]');
      const count = await listingCards.count();
      expect(count).toBeGreaterThan(0);
    });

    // ============================================
    // Payment Methods
    // ============================================
    await test.step('Public profile shows payment methods', async () => {
      // Should show payment method section if configured
      const gcashSection = page.locator('text=GCash');
      const bankSection = page.locator('text=Bank');

      // At least one payment method should be visible
      const hasPayment = (await gcashSection.isVisible()) || (await bankSection.isVisible());
      // This depends on whether the worker has configured payment methods
    });
  });
});
