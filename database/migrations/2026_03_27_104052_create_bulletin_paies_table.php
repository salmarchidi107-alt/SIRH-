<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('bulletin_paies')) {
            Schema::create('bulletin_paies', function (Blueprint $table) {
                $table->id();
                $table->uuid('tenant_id')->nullable()->index();
                $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
                $table->string('periode');
                $table->decimal('salaire_base', 10, 2);
                $table->decimal('prime_anciennete', 10, 2)->default(0);
                $table->decimal('salaire_brut', 10, 2);
                $table->decimal('cotisation_cnss', 10, 2);
                $table->decimal('cotisation_amo', 10, 2);
                $table->decimal('frais_professionnels', 10, 2);
                $table->decimal('salaire_net_imposable', 10, 2);
                $table->decimal('ir', 10, 2)->default(0);
                $table->decimal('net_a_payer', 10, 2);
                $table->string('statut')->default('genere');
                $table->timestamps();
    
                $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
                $table->unique(['employee_id', 'periode', 'tenant_id'], 'bulletin_paies_employee_periode_tenant_unique');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('bulletin_paies');
    }
};
