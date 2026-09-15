<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::firstOrCreate(
            ['email' => 'admin@nexora.com'],
            [
                'name' => 'Nexora Admin',
                'password' => Hash::make('password'), // change immediately after first login
                'designation' => 'System Administrator',
                'status' => 'active',
                'email_verified_at' => now(),
            ]
        );

        $superAdminRole = Role::where('slug', 'super-admin')->first();
        if ($superAdminRole && ! $admin->roles->contains($superAdminRole->id)) {
            $admin->roles()->attach($superAdminRole);
        }
    }
}
