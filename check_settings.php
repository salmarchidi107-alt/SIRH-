<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

try {
    $count = DB::table('settings')->count();
    echo "Settings count: $count\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

