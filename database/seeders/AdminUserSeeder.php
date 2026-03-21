<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $email = env('ADMIN_EMAIL', 'admin@morrisonsph.com');
        $name  = env('ADMIN_NAME', 'Admin');
        $password = env('ADMIN_PASSWORD', 'M0rris0nAdm1n2026!'); // fallback for local testing only

        // Prevent running with default/fallback password in production
        if (app()->environment('production') && $password === 'M0rris0nAdm1n2026!') {
            $this->command->error('Cannot seed admin user in production with fallback password.');
            $this->command->error('Please set ADMIN_PASSWORD in your .env file.');
            return;
        }

        User::updateOrCreate(
            ['email' => $email], // unique identifier
            [
                'name'     => $name,
                'password' => Hash::make($password),
                // Do NOT include 'is_admin' — column does not exist
                // Add any other fields you want to set/ensure, e.g.:
                // 'referral_code' => 'ADMINREF123',
                // 'balance_cents' => 0,
            ]
        );

        $this->command->info("Admin user ensured: {$email}");
    }
}
