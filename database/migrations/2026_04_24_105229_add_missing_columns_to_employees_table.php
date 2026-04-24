<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::table('employees', function (Blueprint $table) {
        if (!Schema::hasColumn('employees', 'sort_order')) {
            $table->unsignedInteger('sort_order')->default(0)->after('position');
        }
        if (!Schema::hasColumn('employees', 'pin')) {
            $table->string('pin')->nullable()->after('user_id');
        }
        if (!Schema::hasColumn('employees', 'plain_pin')) {
            $table->string('plain_pin')->nullable()->after('pin');
        }
        if (!Schema::hasColumn('employees', 'signature')) {
            $table->text('signature')->nullable()->after('plain_pin');
        }
        if (!Schema::hasColumn('employees', 'department_id')) {
            $table->foreignId('department_id')->nullable()->after('department')
                  ->constrained('departments')->nullOnDelete();
        }
        // Corriger payment_method
        $table->string('payment_method')->nullable()->change();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            //
        });
    }
};
