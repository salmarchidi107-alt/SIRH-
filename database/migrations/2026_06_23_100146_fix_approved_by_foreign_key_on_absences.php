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
    Schema::table('absences', function (Blueprint $table) {
        $table->dropForeign('absences_approved_by_foreign');
        $table->foreign('approved_by')->references('id')->on('users')->nullOnDelete();
    });
}

public function down(): void
{
    Schema::table('absences', function (Blueprint $table) {
        $table->dropForeign(['approved_by']);
        $table->foreign('approved_by')->references('id')->on('employees')->nullOnDelete();
    });
}
};
