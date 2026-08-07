<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('data_leak_alerts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('user_name')->nullable();
            $table->string('user_email')->nullable();

            $table->unsignedBigInteger('expected_tenant_id')->nullable();
            $table->string('expected_tenant_name')->nullable();
            $table->unsignedBigInteger('leaked_tenant_id')->nullable();
            $table->string('leaked_tenant_name')->nullable();

            $table->string('module')->nullable();          // ex: "employees"
            $table->string('route_name')->nullable();      // ex: "employees.index"
            $table->string('controller_action')->nullable();// ex: "App\Http\Controllers\EmployeeController@ajax"
            $table->string('url', 2048)->nullable();

            $table->unsignedInteger('rows_count')->default(0);
            $table->json('row_ids')->nullable();

            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();

            $table->timestamp('created_at')->useCurrent();

            $table->index(['expected_tenant_id']);
            $table->index(['leaked_tenant_id']);
            $table->index(['module']);
            $table->index(['created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('data_leak_alerts');
    }
};
