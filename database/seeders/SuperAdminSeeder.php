<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'admin@pulsewire.demo'],
            [
                'name'      => 'Demo Admin',
                'password'  => Hash::make('DemoAdmin123!'),
                'user_type' => 'superadmin',
                'is_active' => true,
            ]
        );
    }
}
