<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        // ── Create default Admin ──────────────────────────────────────────────
        DB::table('logins')->updateOrInsert(
            ['username' => 'admin'],
            [
                'username'   => 'admin',
                'password'   => Hash::make('admin123'),
                'role'       => 'Admin',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        $this->command->info('✅ Default Admin created:');
        $this->command->info('   Username : admin');
        $this->command->info('   Password : admin123');
        $this->command->info('   Role     : Admin');
        $this->command->warn('⚠️  Change the password after first login!');
    }
}