<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;


class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // ========================================
        // PERMISSIONS
        // ========================================

        // User permissions
        $permissions = [
            // User management
            'users.view',
            'users.create',
            'users.update',
            'users.delete',

            // Product management
            'products.view',
            'products.create',
            'products.update',
            'products.delete',

            // Subscription management
            'subscriptions.view',
            'subscriptions.view-all',
            'subscriptions.create',
            'subscriptions.update',
            'subscriptions.cancel',
            'subscriptions.export',

            // Payment management
            'payments.view',
            'payments.view-all',
            'payments.create',
            'payments.refund',
            'payments.export',

            // Reports
            'reports.view',
            'reports.export',

            // System settings
            'settings.view',
            'settings.update',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // ========================================
        // ROLES
        // ========================================

        // Admin Role - Full access
        $adminRole = Role::create(['name' => 'admin']);
        $adminRole->givePermissionTo(Permission::all());

        // Manager Role - Product and subscription management
        $managerRole = Role::create(['name' => 'manager']);
        $managerRole->givePermissionTo([
            'products.view',
            'products.create',
            'products.update',
            'subscriptions.view',
            'subscriptions.view-all',
            'subscriptions.update',
            'subscriptions.cancel',
            'subscriptions.export',
            'payments.view',
            'payments.view-all',
            'payments.export',
            'reports.view',
            'reports.export',
        ]);

        // User Role - Basic customer permissions
        $userRole = Role::create(['name' => 'user']);
        $userRole->givePermissionTo([
            'products.view',
            'subscriptions.view',
            'subscriptions.create',
            'subscriptions.cancel',
            'payments.view',
            'payments.create',
        ]);
    }
}
