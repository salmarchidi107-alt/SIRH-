<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('variable_elements', function (Blueprint $table) {
            $table->string('category');
            $table->string('rubrique');
            $table->string('unit')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('variable_elements', function (Blueprint $table) {
            $table->dropColumn(['category', 'rubrique', 'unit']);
        });
    }
};

