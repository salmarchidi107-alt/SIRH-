<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->string('slug')->nullable()->change();
            $table->enum('plan', ['starter', 'pro', 'enterprise'])->nullable()->change();
            $table->enum('status', ['trial', 'active', 'suspended'])->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->string('slug')->nullable(false)->change();
            $table->enum('plan', ['starter', 'pro', 'enterprise'])->default('starter')->change();
            $table->enum('status', ['trial', 'active', 'suspended'])->default('trial')->change();
        });
    }
};

