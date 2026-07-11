<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Define all permissions
        $permissions = [
            // Dashboard
            'view-dashboard',
            
            // Products
            'view-products',
            'create-products',
            'edit-products',
            'delete-products',
            
            // Materials
            'view-materials',
            'create-materials',
            'edit-materials',
            'delete-materials',
            
            // Contacts
            'view-contacts',
            'create-contacts',
            'edit-contacts',
            'delete-contacts',
            
            // Purchases
            'view-purchases',
            'create-purchases',
            
            // Sales
            'view-sales',
            'create-sales',
            'update-sales-status',
            'print-sales',
            
            // Orders (Public B2C/B2B orders)
            'view-orders',
            'manage-orders',
            
            // Productions
            'view-productions',
            'create-productions',
            'manage-artisan-jobs',
            
            // Stock
            'view-stock',
            'adjust-stock',
            
            // Finance
            'view-finance',
            'manage-accounts',
            'record-transactions',
            'print-finance-reports',
            
            // Payments
            'record-payments',
            
            // Gallery
            'view-gallery',
            'manage-gallery',
            
            // Settings
            'view-settings',
            'manage-settings',
            
            // User Management (Owner & Super Admin only)
            'view-users',
            'manage-users',
            'manage-roles',
            
            // Customer Portal (B2C)
            'view-catalog',
            'place-orders',
            'view-my-orders',
            'view-my-profile',
            'manage-my-profile',
            
            // Partner Portal (B2B/Reseller)
            'view-reseller-prices',
            'view-my-billing',
            'quick-order',
            'view-partner-dashboard',

            // Artisan Portal
            'view-artisan-portal',
        ];

        // Create permissions
        foreach ($permissions as $permissionName) {
            Permission::firstOrCreate(['name' => $permissionName]);
        }

        // Assign permissions to roles
        
        // Operator: Basic operational permissions
        $operatorRole = Role::where('name', 'operator')->first();
        if ($operatorRole) {
            $operatorPermissions = [
                'view-dashboard',
                'view-products',
                'view-materials',
                'view-contacts',
                'view-purchases',
                'create-purchases',
                'view-sales',
                'create-sales',
                'update-sales-status',
                'print-sales',
                'view-productions',
                'create-productions',
                'manage-artisan-jobs',
                'view-stock',
                'adjust-stock',
                'record-payments',
                'view-gallery',
                'view-orders',
                'manage-orders',
            ];
            
            $operatorRole->permissions()->sync(
                Permission::whereIn('name', $operatorPermissions)->pluck('id')
            );
        }

        // Owner: All permissions except user management
        $ownerRole = Role::where('name', 'owner')->first();
        if ($ownerRole) {
            $ownerPermissions = [
                'view-dashboard',
                'view-products',
                'create-products',
                'edit-products',
                'delete-products',
                'view-materials',
                'create-materials',
                'edit-materials',
                'delete-materials',
                'view-contacts',
                'create-contacts',
                'edit-contacts',
                'delete-contacts',
                'view-purchases',
                'create-purchases',
                'view-sales',
                'create-sales',
                'update-sales-status',
                'print-sales',
                'view-productions',
                'create-productions',
                'manage-artisan-jobs',
                'view-stock',
                'adjust-stock',
                'view-finance',
                'manage-accounts',
                'record-transactions',
                'print-finance-reports',
                'record-payments',
                'view-gallery',
                'manage-gallery',
                'view-settings',
                'manage-settings',
                'view-orders',
                'manage-orders',
            ];
            
            $ownerRole->permissions()->sync(
                Permission::whereIn('name', $ownerPermissions)->pluck('id')
            );
        }

        // Super Admin: All permissions (handled by Gate::before)
        // But we can still assign them for clarity
        $superAdminRole = Role::where('name', 'super-admin')->first();
        if ($superAdminRole) {
            $superAdminRole->permissions()->sync(
                Permission::where('name', '!=', 'manage-roles')->pluck('id')
            );
        }
        
        // Customer: B2C/Eceran permissions
        $customerRole = Role::where('name', 'customer')->first();
        if ($customerRole) {
            $customerPermissions = [
                'view-catalog',
                'place-orders',
                'view-my-orders',
                'view-my-profile',
                'manage-my-profile',
            ];
            
            $customerRole->permissions()->sync(
                Permission::whereIn('name', $customerPermissions)->pluck('id')
            );
        }
        
        // Reseller: B2B/Reseller permissions
        $partnerRole = Role::where('name', 'reseller')->first();
        if ($partnerRole) {
            $partnerPermissions = [
                'view-catalog',
                'view-reseller-prices',
                'place-orders',
                'view-my-orders',
                'view-my-billing',
                'quick-order',
                'view-partner-dashboard',
                'view-my-profile',
                'manage-my-profile',
            ];
            
            $partnerRole->permissions()->sync(
                Permission::whereIn('name', $partnerPermissions)->pluck('id')
            );
        }

        // Artisan: workshop worker portal permissions
        $artisanRole = Role::firstOrCreate(['name' => 'pengrajin']);
        $artisanPermissions = [
            'view-artisan-portal',
        ];

        $artisanRole->permissions()->sync(
            Permission::whereIn('name', $artisanPermissions)->pluck('id')
        );
    }
}
