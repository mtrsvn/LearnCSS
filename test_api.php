<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$r = Illuminate\Support\Facades\Http::withBasicAuth(env('XENDIT_SECRET_KEY'), '')
    ->post('https://api.xendit.co/v2/invoices', [
        'external_id' => 'TEST-12345678',
        'amount' => 299,
        'payer_email' => 'test@gmail.com',
        'description' => 'Test'
    ]);
var_dump($r->successful());
var_dump($r->json());
