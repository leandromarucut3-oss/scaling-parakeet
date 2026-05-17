<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class AdminBalanceSeeder extends Seeder
{
    public function run()
    {
        $admins = User::role('admin')->get();
        $this->command->info('Found '.$admins->count().' admin(s)');

        foreach ($admins as $admin) {
            $admin->balance_cents = 100000000; // $1,000,000.00
            $admin->save();
            $this->command->info('Updated user id: '.$admin->id);
        }
    }
}
