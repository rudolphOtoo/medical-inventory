<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * Baseline structure (departments) is always planted so the application has
     * the organisational skeleton it needs in every environment. Demo users and
     * records are only created when running locally or when the
     * `SEED_DEMO_USERS=true` flag is explicitly set, so a production deployment
     * never receives sample credentials. Production administrator accounts are
     * created by `medtrack:provision-admin`.
     */
    public function run(): void
    {
        $this->call(BaselineSeeder::class);

        if ($this->shouldSeedDemoUsers()) {
            $this->call(DemoSeeder::class);

            return;
        }

        optional($this->command)->warn('Demo data skipped. Only baseline structure has been seeded.');
    }

    /**
     * Determine whether demo users and sample records should be planted.
     */
    protected function shouldSeedDemoUsers(): bool
    {
        if (app()->environment('local')) {
            return true;
        }

        return (bool) config('medtrack.seed_demo_users');
    }
}
