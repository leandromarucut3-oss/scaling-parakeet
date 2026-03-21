<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$deleted = \App\Models\User::where('name', 'Admin')->delete();
echo "Deleted $deleted user(s) with name 'Admin'.\n";
?>
