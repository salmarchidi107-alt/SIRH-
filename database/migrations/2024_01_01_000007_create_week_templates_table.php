<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('week_templates', function (Blueprint $table) {
            $table->id();
            $table->uuid('tenant_id');
            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->string('name');
            $table->string('monday_shift_type')->nullable();
            $table->time('monday_start')->nullable();
            $table->time('monday_end')->nullable();
            $table->string('tuesday_shift_type')->nullable();
            $table->time('tuesday_start')->nullable();
            $table->time('tuesday_end')->nullable();
            $table->string('wednesday_shift_type')->nullable();
            $table->time('wednesday_start')->nullable();
            $table->time('wednesday_end')->nullable();
            $table->string('thursday_shift_type')->nullable();
            $table->time('thursday_start')->nullable();
            $table->time('thursday_end')->nullable();
            $table->string('friday_shift_type')->nullable();
            $table->time('friday_start')->nullable();
            $table->time('friday_end')->nullable();
            $table->string('saturday_shift_type')->nullable();
            $table->time('saturday_start')->nullable();
            $table->time('saturday_end')->nullable();
            $table->string('sunday_shift_type')->nullable();
            $table->time('sunday_start')->nullable();
            $table->time('sunday_end')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('week_templates');
    }
};
