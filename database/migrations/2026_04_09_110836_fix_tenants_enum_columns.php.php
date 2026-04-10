<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // ✅ Modifier les enums pour correspondre aux valeurs PHP
        DB::statement("ALTER TABLE tenants MODIFY COLUMN plan ENUM('starter','pro','enterprise') DEFAULT 'starter'");
        DB::statement("ALTER TABLE tenants MODIFY COLUMN status ENUM('active','suspended','trial','inactive') DEFAULT 'inactive'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE tenants MODIFY COLUMN plan ENUM('starter','pro','enterprise') DEFAULT NULL");
        DB::statement("ALTER TABLE tenants MODIFY COLUMN status ENUM('trial','active','suspended') DEFAULT NULL");
    }
};
