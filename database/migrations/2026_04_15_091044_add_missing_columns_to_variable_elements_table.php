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
            $table->foreignId('employee_id')->nullable()->constrained('employees')->onDelete('cascade');
            $table->tinyInteger('month')->unsigned()->nullable();
            $table->smallInteger('year')->unsigned()->nullable();
            $table->string('label')->nullable();
            $table->decimal('amount', 10, 2)->nullable();
            $table->enum('type', ['gain', 'deduction'])->nullable();
            $table->index(['employee_id']);
            $table->index(['month', 'year']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('variable_elements', function (Blueprint $table) {
            $table->dropForeign(['employee_id']);
            $table->dropColumn(['employee_id', 'month', 'year', 'label', 'amount', 'type']);
            $table->dropIndex(['employee_id']);
            $table->dropIndex(['month', 'year']);
        });
    }
};
