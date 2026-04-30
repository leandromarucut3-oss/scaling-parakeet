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
        // Seed roles and the admin user first
        $this->call([
            RoleSeeder::class,
            AdminUserSeeder::class,
        ]);

        if (!app()->environment('production')) {
            $testUsers = [
                [
                    'email' => env('TEST_USER_EMAIL', 'testuser@example.com'),
                    'name' => env('TEST_USER_NAME', 'Mico'),
                    'password' => Hash::make(env('TEST_USER_PASSWORD', 'Abc123456')),
                ],
                [
                    'email' => env('TEST_USER2_EMAIL', 'testuser2@example.com'),
                    'name' => env('TEST_USER2_NAME', 'Tess'),
                    'password' => Hash::make(env('TEST_USER2_PASSWORD', 'Abc123456')),
                ],
            ];

            foreach ($testUsers as $userData) {
                $user = User::firstOrCreate(
                    ['email' => $userData['email']],
                    [
                        'name' => $userData['name'],
                        'password' => $userData['password'],
                    ]
                );

                if (method_exists($user, 'assignRole')) {
                    $user->assignRole('user');
                }
            }
        }
    }
}
