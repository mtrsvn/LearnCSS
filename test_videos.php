<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$topics = App\Models\Topic::orderBy('sort_order')->get(['id', 'title', 'videos'])->toArray();
file_put_contents('test_videos.json', json_encode($topics, JSON_PRETTY_PRINT));
echo "Done";
