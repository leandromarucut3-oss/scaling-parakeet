<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run()
    {
        User::updateOrCreate(
            ['email' => env('ADMIN_EMAIL')], // check by email
            [
                'name' => env('ADMIN_NAME'),
                'password' => Hash::make(env('ADMIN_PASSWORD')),
                'is_admin' => 1, // make sure admin flag is set
            ]
        );

        $this->command->info('Admin user ensured.');
    }
}
