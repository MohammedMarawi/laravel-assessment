<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;


class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    
    public function run(): void
    {
        $this->call([
            // 1. Setup Roles & Permissions first
            RolePermissionSeeder::class,

            // 2. Create Users (depends on Roles)
            UserSeeder::class,

            // 3. Create Products (independent)
            ProductSeeder::class,

            // 4. Create Subscriptions (depends on Users & Products)
            SubscriptionSeeder::class,

            // 5. Create Payments (depends on Users & Subscriptions)
            PaymentSeeder::class,
        ]);
    }
}
