<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;


class UserSeeder extends Seeder
{
    public function run(): void
    {
        // ========================================
        // ADMIN USER
        // ========================================
        $admin = User::create([
            'name' => 'System Administrator',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
        $admin->assignRole('admin');

        // ========================================
        // MANAGER USERS
        // ========================================
        $manager1 = User::create([
            'name' => 'Sarah Manager',
            'email' => 'manager@example.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
        $manager1->assignRole('manager');

        $manager2 = User::create([
            'name' => 'John Manager',
            'email' => 'john.manager@example.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
        $manager2->assignRole('manager');

        // ========================================
        // CUSTOMER USERS - Different Scenarios
        // ========================================

        // Customer with active subscription
        $activeCustomer = User::create([
            'name' => 'Ahmed Active Customer',
            'email' => 'ahmed@example.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
        $activeCustomer->assignRole('user');

        // Customer with expired subscription
        $expiredCustomer = User::create([
            'name' => 'Fatima Expired Customer',
            'email' => 'fatima@example.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
        $expiredCustomer->assignRole('user');

        // Customer with cancelled subscription
        $cancelledCustomer = User::create([
            'name' => 'Omar Cancelled Customer',
            'email' => 'omar@example.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
        $cancelledCustomer->assignRole('user');

        // Customer with pending subscription (waiting for payment)
        $pendingCustomer = User::create([
            'name' => 'Layla Pending Customer',
            'email' => 'layla@example.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
        $pendingCustomer->assignRole('user');

        // Customer with failed payment
        $failedPaymentCustomer = User::create([
            'name' => 'Khalid Failed Payment',
            'email' => 'khalid@example.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
        $failedPaymentCustomer->assignRole('user');

        // Customer with multiple subscriptions
        $multiSubCustomer = User::create([
            'name' => 'Noor Multi Subscriber',
            'email' => 'noor@example.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
        $multiSubCustomer->assignRole('user');

        // New customer (no subscriptions yet)
        $newCustomer = User::create([
            'name' => 'Ali New Customer',
            'email' => 'ali@example.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
        $newCustomer->assignRole('user');

        // Trial customer
        $trialCustomer = User::create([
            'name' => 'Mona Trial Customer',
            'email' => 'mona@example.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
        $trialCustomer->assignRole('user');

        // VIP Customer with long history
        $vipCustomer = User::create([
            'name' => 'Youssef VIP Customer',
            'email' => 'youssef@example.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
        $vipCustomer->assignRole('user');

        // Customer with refunded payment
        $refundedCustomer = User::create([
            'name' => 'Hana Refunded Customer',
            'email' => 'hana@example.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
        $refundedCustomer->assignRole('user');

        // ========================================
        // ADDITIONAL RANDOM USERS
        // ========================================
        User::factory()->count(10)->create()->each(function ($user) {
            $user->assignRole('user');
        });
    }
}
