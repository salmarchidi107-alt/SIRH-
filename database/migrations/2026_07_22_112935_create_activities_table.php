<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activities', function (Blueprint $table) {
            $table->id();

            $table->string('tenant_id')->nullable()->index();

            $table->foreignId('task_id')->constrained()->cascadeOnDelete();

            // Redondant avec task.user_id mais utile pour l'historique / les rapports RH
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            // 'manuelle' = saisie libre par l'employé, 'chrono' = générée par le chronomètre
            $table->enum('type', ['manuelle', 'chrono'])->default('manuelle');

            $table->date('activity_date');
            $table->unsignedInteger('duration_minutes');
            $table->text('comment')->nullable();
            $table->string('attachment_path')->nullable();

            $table->timestamps();

            $table->index(['tenant_id', 'task_id', 'activity_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activities');
    }
};
