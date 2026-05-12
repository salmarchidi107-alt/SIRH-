<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // plan et status seront supprimés par update_tenants_table_structure — rien à faire
    }

    public function down(): void {}
};
