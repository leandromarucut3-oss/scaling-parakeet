<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$deleted = \App\Models\User::where('email', 'admin@morrisonsph.com')->delete();
echo "Deleted $deleted user(s).\n";
?>
