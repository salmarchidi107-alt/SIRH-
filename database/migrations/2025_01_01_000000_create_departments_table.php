<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('departments')) {
            Schema::create('departments', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->timestamps();
            });
        }

        if (!Schema::hasColumn('employees', 'department_id')) {
            Schema::table('employees', function (Blueprint $table) {
                $table->foreignId('department_id')
                    ->nullable()
                    ->after('department')
                    ->constrained('departments')
                    ->nullOnDelete();
            });
        }

        // Peupler les départements depuis les employés existants
        $existingDepartments = DB::table('employees')
            ->select('department')
            ->distinct()
            ->whereNotNull('department')
            ->pluck('department');

        foreach ($existingDepartments as $name) {
            if (!empty(trim($name))) {
                DB::table('departments')->insertOrIgnore([
                    'name'       => trim($name),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        $departments = DB::table('departments')->pluck('id', 'name');
        foreach ($departments as $name => $id) {
            DB::table('employees')->where('department', $name)->update(['department_id' => $id]);
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('employees', 'department_id')) {
            Schema::table('employees', function (Blueprint $table) {
                $table->dropConstrainedForeignId('department_id');
            });
        }
        Schema::dropIfExists('departments');
    }
};
