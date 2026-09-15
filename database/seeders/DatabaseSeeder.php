<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed order matters: roles/permissions and lookup tables (lead
     * statuses/sources, deal stages) must exist before the admin user
     * and any demo records reference them.
     */
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            LeadLookupSeeder::class,
            DealStageSeeder::class,
            UserSeeder::class,
            DemoDataSeeder::class,
        ]);
    }
}
