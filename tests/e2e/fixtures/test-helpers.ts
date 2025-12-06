import { Page, expect } from '@playwright/test';

// Test user credentials (from database seeders)
export const testUsers = {
  customer: {
    name: 'Miguel Torres',
    email: 'miguel.torres@example.com',
    password: 'password',
  },
  worker: {
    name: 'Lisa Mendoza',
    email: 'lisa.mendoza@example.com',
    password: 'password',
  },
  admin: {
    name: 'Admin',
    email: 'admin@kayakoyan.com',
    password: 'password',
  },
  // Alias for clarity
  seededWorker: {
    name: 'Lisa Mendoza',
    email: 'lisa.mendoza@example.com',
    password: 'password',
  },
  seededCustomer: {
    name: 'Miguel Torres',
    email: 'miguel.torres@example.com',
    password: 'password',
  },
};

/**
 * Login as a specific user type
 */
export async function loginAs(page: Page, userType: keyof typeof testUsers) {
  const user = testUsers[userType];
  await page.goto('/login');
  await page.fill('input[name="email"]', user.email);
  await page.fill('input[name="password"]', user.password);
  await page.click('button[type="submit"]');
}

/**
 * Login with custom credentials
 */
export async function loginWithCredentials(page: Page, email: string, password: string) {
  await page.goto('/login');
  await page.fill('input[name="email"]', email);
  await page.fill('input[name="password"]', password);
  await page.click('button[type="submit"]');
}

/**
 * Register a new customer
 */
export async function registerCustomer(page: Page, name: string, email: string, password: string) {
  await page.goto('/register');
  await page.fill('input[name="name"]', name);
  await page.fill('input[name="email"]', email);
  await page.fill('input[name="password"]', password);
  await page.fill('input[name="password_confirmation"]', password);
  await page.click('button[type="submit"]');
}

/**
 * Register a new worker
 */
export async function registerWorker(page: Page, name: string, email: string, password: string) {
  await page.goto('/become-a-worker');
  await page.fill('input[name="name"]', name);
  await page.fill('input[name="email"]', email);
  await page.fill('input[name="password"]', password);
  await page.fill('input[name="password_confirmation"]', password);
  await page.click('button[type="submit"]');
}

/**
 * Logout current user
 */
export async function logout(page: Page) {
  // Find and click logout button/form
  await page.click('button:has-text("Logout"), form[action*="logout"] button');
}

/**
 * Get the verification link from Laravel logs
 * Note: This requires accessing the log file from the test
 */
export async function getVerificationLinkFromLogs(): Promise<string | null> {
  // In a real setup, you'd parse the Laravel log file
  // For now, this is a placeholder that would need backend integration
  return null;
}

/**
 * Navigate to a listing and create an order
 */
export async function createOrderFromListing(page: Page, listingSlug: string, notes?: string) {
  await page.goto(`/listings/${listingSlug}`);

  if (notes) {
    await page.fill('textarea[name="notes"]', notes);
  }

  await page.click('button:has-text("Order Now"), button:has-text("Place Order")');
}

/**
 * Submit payment for an order
 */
export async function submitPayment(
  page: Page,
  orderId: number,
  method: 'gcash' | 'bank_transfer',
  referenceNumber: string,
  proofPath: string
) {
  await page.goto(`/customer/orders/${orderId}/payment`);

  // Select payment method
  await page.selectOption('select[name="method"]', method);

  // Fill reference number
  await page.fill('input[name="reference_number"]', referenceNumber);

  // Upload proof
  await page.setInputFiles('input[name="proof"]', proofPath);

  await page.click('button[type="submit"]');
}

/**
 * Wait for page to be fully loaded
 */
export async function waitForPageLoad(page: Page) {
  await page.waitForLoadState('networkidle');
}

/**
 * Check if user is on the expected dashboard based on role
 */
export async function expectDashboard(page: Page, role: 'customer' | 'worker' | 'admin') {
  switch (role) {
    case 'customer':
      await expect(page).toHaveURL(/\/customer\/dashboard/);
      break;
    case 'worker':
      await expect(page).toHaveURL(/\/worker/);
      break;
    case 'admin':
      await expect(page).toHaveURL(/\/admin/);
      break;
  }
}

/**
 * Generate a unique email for test isolation
 */
export function generateTestEmail(prefix: string = 'test'): string {
  const timestamp = Date.now();
  const random = Math.random().toString(36).substring(7);
  return `${prefix}-${timestamp}-${random}@example.com`;
}

/**
 * Generate a unique name for test isolation
 */
export function generateTestName(prefix: string = 'Test User'): string {
  const random = Math.random().toString(36).substring(7);
  return `${prefix} ${random}`;
}
