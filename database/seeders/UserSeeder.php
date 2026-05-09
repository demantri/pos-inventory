<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'name'     => 'Super Admin',
                'email'    => 'superadmin@pos.test',
                'password' => Hash::make('password'),
                'is_active'=> true,
                'role'     => 'super_admin',
            ],
            [
                'name'     => 'Admin',
                'email'    => 'admin@pos.test',
                'password' => Hash::make('password'),
                'is_active'=> true,
                'role'     => 'admin',
            ],
            [
                'name'     => 'Kasir Demo',
                'email'    => 'kasir@pos.test',
                'password' => Hash::make('password'),
                'is_active'=> true,
                'role'     => 'kasir',
            ],
            [
                'name'     => 'Gudang Demo',
                'email'    => 'gudang@pos.test',
                'password' => Hash::make('password'),
                'is_active'=> true,
                'role'     => 'gudang',
            ],
        ];

        foreach ($users as $data) {
            $role = $data['role'];
            unset($data['role']);

            $user = User::firstOrCreate(['email' => $data['email']], $data);
            $user->syncRoles([$role]);

            $this->command->info("  → {$user->name} ({$user->email}) | role: {$role}");
        }

        $this->command->info('✅ User demo berhasil dibuat.');
    }
}
