<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;

// Delete any existing admin
User::where('email', 'admin@morrisonsph.com')->delete();

// Create fresh admin user
$admin = User::create([
    'email' => 'admin@morrisonsph.com',
    'name' => 'Admin',
    'password' => Hash::make('Admin123456'),
    'balance_cents' => 1000000000,
]);

// Assign admin role
if (method_exists($admin, 'assignRole')) {
    $admin->assignRole('admin');
}

echo "✓ Admin user created successfully!\n";
echo "Email: admin@morrisonsph.com\n";
echo "Password: Admin123456\n";
?>
