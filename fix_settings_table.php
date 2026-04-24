<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

// Drop and recreate settings table with auto-incrementing id
Schema::dropIfExists('settings');

Schema::create('settings', function (Blueprint $table) {
    $table->id();
    $table->string('group');
    $table->string('name');
    $table->json('payload');
    $table->timestamp('created_at')->useCurrent();
    $table->timestamp('updated_at')->useCurrent();

    $table->unique(['group', 'name']);
    $table->index(['group', 'name']);
});

echo "Settings table fixed successfully.\n";

