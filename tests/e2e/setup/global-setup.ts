import { execSync } from 'child_process';

/**
 * Global setup that runs before all E2E tests.
 * Resets the database to a clean state with seeded data.
 */
async function globalSetup() {
  console.log('\n📦 Resetting database for E2E tests...\n');

  try {
    // Reset database and run seeders
    execSync('docker compose exec -T app php artisan migrate:fresh --seed --force', {
      stdio: 'inherit',
      cwd: '/root/Development/kayakoyan'
    });

    console.log('\n✅ Database reset complete!\n');
  } catch (error) {
    console.error('\n❌ Failed to reset database:', error);
    throw error;
  }
}

export default globalSetup;
