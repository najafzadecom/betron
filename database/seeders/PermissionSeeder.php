<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Sistem controllerlərində istifadə olunan bütün permissionlar
        $permissions = [

            // User permissions
//            'users-index',
//            'users-create',
//            'users-edit',
//            'users-delete',
//            'users-show',
//            'users-profile',
//
//            // Role permissions
//            'roles-index',
//            'roles-create',
//            'roles-edit',
//            'roles-delete',
//            'roles-show',
//
//            // Permission permissions
//            'permissions-index',
//            'permissions-create',
//            'permissions-edit',
//            'permissions-delete',
//            'permissions-show',
//
//            // Merchant permissions
//            'merchants-index',
//            'merchants-create',
//            'merchants-edit',
//            'merchants-delete',
//            'merchants-show',
//
//            // Wallet permissions
//            'wallets-index',
//            'wallets-create',
//            'wallets-edit',
//            'wallets-delete',
//            'wallets-show',
//
//            // Transaction permissions
//            'transactions-index',
//            'transactions-create',
//            'transactions-edit',
//            'transactions-delete',
//            'transactions-show',
//
//            // Provider permissions
//            'providers-index',
//            'providers-create',
//            'providers-edit',
//            'providers-delete',
//            'providers-show',
//
//            // Bank permissions
//            'banks-index',
//            'banks-create',
//            'banks-edit',
//            'banks-delete',
//            'banks-show',
//
//            // Withdrawal permissions
//            'withdrawals-index',
//            'withdrawals-create',
//            'withdrawals-edit',
//            'withdrawals-delete',
//            'withdrawals-show',
//            'withdrawals-send',
//
//            // Activity Log permissions (yalnız index)
//            'activity-logs-index',
//            'activity-logs-show',
//
//            'banks-index',
//            'banks-create',
//            'banks-edit',
//            'banks-delete',
//            'banks-show',
//
//            'blacklists-index',
//            'blacklists-create',
//            'blacklists-edit',
//            'blacklists-delete',
//            'blacklists-show',
//
//            'sites-index',
//            'sites-create',
//            'sites-edit',
//            'sites-delete',
//            'sites-show',
//
//            // Statistics permissions
//            'statistics-index',
//
//            // Settings permissions
//            'settings-index',

            // Vendors permissions (Admin panel)
            'vendors-index',
            'vendors-create',
            'vendors-edit',
            'vendors-delete',
            'vendors-show',

            // Vendor Users permissions (Admin panel)
            'vendor-users-index',
            'vendor-users-create',
            'vendor-users-edit',
            'vendor-users-delete',
            'vendor-users-show',
        ];

        foreach ($permissions as $permission) {
            Permission::query()->firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ]);
        }

        $this->command->info('Admin permissionlar uğurla yaradıldı!');

        // Vendor guard permissions
        $vendorPermissions = [
            // Dashboard
            'vendor-dashboard',

            // Vendor Users Management
            'vendor-users-index',
            'vendor-users-create',
            'vendor-users-edit',
            'vendor-users-delete',
            'vendor-users-show',

            // Vendor Roles Management
            'vendor-roles-index',
            'vendor-roles-create',
            'vendor-roles-edit',
            'vendor-roles-delete',
            'vendor-roles-show',

            // Wallet Management
            'vendor-wallets-index',
            'vendor-wallets-create',
            'vendor-wallets-edit',
            'vendor-wallets-delete',
            'vendor-wallets-show',
            'vendor-wallets-files-upload',
            'vendor-wallets-files-delete',

            // Transaction Management (read-only)
            'vendor-transactions-index',
            'vendor-transactions-show',
            'vendor-transactions-export',

            // Withdrawal Management (read-only)
            'vendor-withdrawals-index',
            'vendor-withdrawals-show',
            'vendor-withdrawals-export',
        ];

        foreach ($vendorPermissions as $permission) {
            Permission::query()->firstOrCreate([
                'name' => $permission,
                'guard_name' => 'vendor',
            ]);
        }

        $this->command->info('Vendor permissionlar uğurla yaradıldı!');
    }
}
