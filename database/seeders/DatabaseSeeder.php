<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RolePermissionSeeder::class,  // 1. Role & permission dulu
            MasterDataSeeder::class,       // 2. Kategori & satuan
            UserSeeder::class,             // 3. User (butuh role sudah ada)
        ]);

        $this->command->info('');
        $this->command->info('🎉 Seeding selesai! Akun login:');
        $this->command->table(
            ['Nama', 'Email', 'Password', 'Role'],
            [
                ['Super Admin', 'superadmin@pos.test', 'password', 'super_admin'],
                ['Admin',       'admin@pos.test',      'password', 'admin'],
                ['Kasir Demo',  'kasir@pos.test',      'password', 'kasir'],
                ['Gudang Demo', 'gudang@pos.test',     'password', 'gudang'],
            ]
        );
    }
}
