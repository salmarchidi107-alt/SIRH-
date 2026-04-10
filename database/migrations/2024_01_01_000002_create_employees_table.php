<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->uuid('tenant_id');
            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->string('matricule')->unique();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->unique();
            $table->string('phone')->nullable();
            $table->string('photo')->nullable();
            $table->string('department')->nullable();
            $table->string('position')->nullable();
            $table->string('diploma_type')->nullable();
            $table->json('skills')->nullable();
            $table->string('contract_type')->nullable();
            $table->date('hire_date')->nullable();
            $table->date('birth_date')->nullable();
            $table->enum('status', ['active', 'inactive', 'on_leave'])->default('active');
            $table->decimal('base_salary', 10, 2)->nullable();
            $table->foreignId('manager_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->string('cnss')->nullable();
            $table->string('cin')->nullable();
            $table->text('address')->nullable();
            $table->string('family_situation')->nullable();
            $table->unsignedInteger('children_count')->default(0);
            $table->enum('payment_method', ['bank', 'cash'])->default('bank');
            $table->string('bank')->nullable();
            $table->string('rib')->nullable();
            $table->decimal('work_hours_counter', 5, 2)->default(0);
            $table->foreignId('user_id')->nullable()->constrained('users');
            $table->date('contract_start_date')->nullable();
            $table->date('contract_end_date')->nullable();
            $table->json('work_days')->nullable();
            $table->unsignedInteger('cp_days')->default(0);
            $table->string('contractual_benefits')->nullable();
            $table->string('emergency_contact')->nullable();
            $table->string('emergency_phone')->nullable();
            $table->decimal('work_hours', 5, 2)->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('employees');
    }
};

