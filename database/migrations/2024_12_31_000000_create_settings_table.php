<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('group');
            $table->string('name');
            $table->json('payload');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent();

            $table->unique(['group', 'name']);
            $table->index(['group', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};

