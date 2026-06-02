<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$u = App\Models\User::find(7);
if ($u) {
    $u->is_admin = 1;
    $u->save();
    echo "Admin user updated!\n";
} else {
    echo "User 7 not found.\n";
}
