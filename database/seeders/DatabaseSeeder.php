<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Call other seeders first
        $this->call(RoleSeeder::class);

        // Create a test user if it doesn't exist
        User::firstOrCreate(
            ['email' => 'testuser@example.com'],
            [
                'name' => 'Mico',
                'password' => Hash::make('Abc123456'),
            ]
        );

        // Create admin user from .env if credentials exist
        $adminEmail = env('ADMIN_EMAIL');
        $adminPassword = env('ADMIN_PASSWORD');
        $adminName = env('ADMIN_NAME', 'Admin');

        if ($adminEmail && $adminPassword) {
            // Use updateOrCreate to ensure admin is created or updated safely
            $admin = User::updateOrCreate(
                ['email' => $adminEmail],
                [
                    'name' => $adminName,
                    'password' => Hash::make($adminPassword),
                    'balance_cents' => 1000000000, // initial balance
                ]
            );

            // Assign admin role if your system uses roles
            if (method_exists($admin, 'assignRole')) {
                $admin->assignRole('admin');
            }
        }
    }
}
