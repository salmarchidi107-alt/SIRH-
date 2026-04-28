<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('matricule')->unique();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->unique();
            $table->string('phone')->nullable();
            $table->string('photo')->nullable();
            $table->string('department');
            $table->string('position');
            $table->enum('contract_type', ['CDI', 'CDD', 'Interim', 'Stage'])->default('CDI');
            $table->date('hire_date');
            $table->date('birth_date')->nullable();
            $table->enum('status', ['active', 'inactive', 'leave'])->default('active');
            $table->decimal('base_salary', 10, 2)->nullable();
            $table->foreignId('manager_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->string('cnss')->nullable();
            $table->string('cin')->nullable();
            $table->string('family_situation')->nullable();
            $table->text('address')->nullable();
            $table->string('emergency_contact')->nullable();
            $table->string('emergency_phone')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
