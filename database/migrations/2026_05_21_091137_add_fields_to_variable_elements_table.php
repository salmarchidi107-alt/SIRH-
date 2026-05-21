<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('variable_elements', function (Blueprint $table) {

            $table->string('category')->nullable();

            $table->string('rubrique')->nullable();

            $table->string('unit')->nullable();

        });
    }

    public function down(): void
    {
        Schema::table('variable_elements', function (Blueprint $table) {

            $table->dropColumn([
                'category',
                'rubrique',
                'unit'
            ]);

        });
    }

};
