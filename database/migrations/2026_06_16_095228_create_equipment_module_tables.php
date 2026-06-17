<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('equipment_categories', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id');
            $table->string('name');
            $table->string('color')->default('blue');
            $table->timestamps();
            $table->index('tenant_id');
        });

        Schema::create('equipments', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id');
            $table->string('reference');
            $table->string('designation');
            $table->foreignId('equipment_category_id')->constrained('equipment_categories');
            $table->string('brand')->nullable();
            $table->string('serial_number')->nullable();
            $table->enum('condition', ['neuf', 'bon_etat', 'usure_normale', 'endommage', 'perdu'])->default('neuf');
            $table->enum('status', ['disponible', 'affecte', 'maintenance', 'hors_service'])->default('disponible');
            $table->decimal('value', 10, 2)->default(0);
            $table->date('purchase_date')->nullable();
            $table->date('warranty_end_date')->nullable();
            $table->timestamps();
            $table->index('tenant_id');
            $table->unique(['tenant_id', 'reference']);
        });

        Schema::create('equipment_assignments', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id');
            $table->foreignId('equipment_id')->constrained('equipments');
            $table->foreignId('employee_id')->constrained('employees');
            $table->date('assigned_at');
            $table->date('returned_at')->nullable();
            $table->enum('condition_at_assignment', ['neuf', 'bon_etat', 'usure_normale']);
            $table->enum('condition_at_return', ['neuf', 'bon_etat', 'usure_normale', 'endommage', 'perdu'])->nullable();
            $table->text('return_notes')->nullable();
            $table->enum('status', ['active', 'returned', 'lost'])->default('active');
            $table->foreignId('assigned_by')->constrained('users');
            $table->timestamps();
            $table->index('tenant_id');
        });

        Schema::create('equipment_discharges', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id');
            $table->string('reference');
            $table->foreignId('equipment_assignment_id')->constrained('equipment_assignments');
            $table->enum('type', ['remise', 'restitution'])->default('remise');
            $table->enum('status', ['en_attente', 'signee'])->default('en_attente');
            $table->string('pdf_path')->nullable();
            $table->timestamp('signed_at')->nullable();
            $table->timestamps();
            $table->index('tenant_id');
            $table->unique(['tenant_id', 'reference']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('equipment_discharges');
        Schema::dropIfExists('equipment_assignments');
        Schema::dropIfExists('equipments');
        Schema::dropIfExists('equipment_categories');
    }
};
