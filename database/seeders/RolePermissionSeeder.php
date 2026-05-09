<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cache permission
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // ── Definisi semua permission ──
        $permissions = [
            // Dashboard
            'dashboard.view',

            // Master Data
            'categories.view', 'categories.create', 'categories.edit', 'categories.delete',
            'units.view',       'units.create',       'units.edit',       'units.delete',
            'products.view',    'products.create',    'products.edit',    'products.delete',
            'suppliers.view',   'suppliers.create',   'suppliers.edit',   'suppliers.delete',
            'customers.view',   'customers.create',   'customers.edit',   'customers.delete',

            // Pembelian
            'purchase_orders.view',   'purchase_orders.create',
            'purchase_orders.edit',   'purchase_orders.delete',
            'goods_receipts.view',    'goods_receipts.create',
            'goods_receipts.confirm',

            // Inventory
            'inventory.view',
            'inventory.adjustment',
            'stock_lots.view',

            // POS / Kasir
            'pos.access',
            'pos.void',

            // Laporan
            'reports.hpp',
            'reports.inventory',
            'reports.sales',
            'reports.profit',

            // User management
            'users.view', 'users.create', 'users.edit', 'users.delete',

            // Settings
            'settings.view', 'settings.store', 'settings.payment_gateway', 'settings.roles',
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }

        // ── Role: Super Admin (semua akses) ──
        $superAdmin = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
        $superAdmin->syncPermissions(Permission::all());

        // ── Role: Admin (hampir semua, minus users.delete) ──
        $admin = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $admin->syncPermissions(Permission::whereNotIn('name', [
            'users.delete',
        ])->get());

        // ── Role: Kasir (hanya POS + master data view) ──
        $kasir = Role::firstOrCreate(['name' => 'kasir', 'guard_name' => 'web']);
        $kasir->syncPermissions([
            'dashboard.view',
            'products.view',
            'customers.view', 'customers.create', 'customers.edit',
            'pos.access',
            'reports.sales',
        ]);

        // ── Role: Gudang (pembelian + inventory) ──
        $gudang = Role::firstOrCreate(['name' => 'gudang', 'guard_name' => 'web']);
        $gudang->syncPermissions([
            'dashboard.view',
            'products.view',
            'suppliers.view',
            'purchase_orders.view', 'purchase_orders.create', 'purchase_orders.edit',
            'goods_receipts.view',  'goods_receipts.create',  'goods_receipts.confirm',
            'inventory.view',
            'inventory.adjustment',
            'stock_lots.view',
            'reports.inventory',
        ]);

        $this->command->info('✅ Role & Permission berhasil dibuat.');
    }
}
