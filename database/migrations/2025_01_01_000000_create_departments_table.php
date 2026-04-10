<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('departments', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->uuid('tenant_id')->index();
            $table->timestamps();
        });

       $tenantId = 'default-superadmin-tenant';

$departments = DB::table('employees')
    ->selectRaw('DISTINCT TRIM(department) as name')
    ->whereNotNull('department')
    ->whereRaw("TRIM(department) != ''")
    ->get();

foreach ($departments as $dept) {
    DB::table('departments')->updateOrInsert(
        [
            'name' => $dept->name,
            'tenant_id' => $tenantId
        ],
        [
            'slug' => Str::slug($dept->name),
            'created_at' => now(),
            'updated_at' => now(),
        ]
    );
}}

    public function down(): void
    {
        Schema::dropIfExists('departments');
    }
};
