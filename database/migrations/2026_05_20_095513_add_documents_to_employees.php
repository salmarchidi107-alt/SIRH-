<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->string('doc_casier_path')->nullable()->after('emergency_phone');
            $table->string('doc_rib_path')->nullable()->after('doc_casier_path');
            $table->string('doc_diplomes_path')->nullable()->after('doc_rib_path');
            $table->string('doc_cin_path')->nullable()->after('doc_diplomes_path');
            $table->string('doc_contrat_path')->nullable()->after('doc_cin_path');
        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn([
                'doc_casier_path',
                'doc_rib_path',
                'doc_diplomes_path',
                'doc_cin_path',
                'doc_contrat_path',
            ]);
        });
    }
};
