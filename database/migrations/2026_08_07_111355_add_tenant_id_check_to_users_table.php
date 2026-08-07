<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement(
            "ALTER TABLE users ADD CONSTRAINT users_tenant_or_superadmin
             CHECK (tenant_id IS NOT NULL OR role = 'superadmin')"
        );
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE users DROP CONSTRAINT users_tenant_or_superadmin');
    }
};
