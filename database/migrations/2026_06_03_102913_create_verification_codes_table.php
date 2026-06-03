<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('verification_codes', function (Blueprint $table) {
            $table->id();

            // Code unique GLOBAL — jamais réutilisé même après révocation/expiration
            $table->string('code', 6)->unique();

            // Appartenance obligatoire — pas de code sans tenant
            $table->string('tenant_id')->index();

            // Trimestre au format T{1-4}-YYYY, ex : T2-2025
            $table->string('quarter', 10);

            // Statut — PENDING supprimé : un code est toujours attribué à un employé
            $table->enum('status', ['assigned', 'used', 'revoked', 'expired'])
                  ->default('assigned');

            // Appartenance obligatoire à un employé
            // nullOnDelete() : conservation de l'historique si l'user est supprimé
            $table->foreignId('user_id')
                  ->nullable()           // nullable pour la FK uniquement, logique applicative interdit null
                  ->constrained('users')
                  ->nullOnDelete();

            // Audit attribution
            $table->foreignId('assigned_by')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();
            $table->timestamp('assigned_at')->nullable();

            // Audit génération
            $table->foreignId('generated_by')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();

            // Utilisation
            $table->timestamp('used_at')->nullable();

            // Révocation
            $table->foreignId('revoked_by')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();
            $table->timestamp('revoked_at')->nullable();
            $table->string('revoke_reason')->nullable();

            // Expiration trimestrielle
            $table->timestamp('expired_at')->nullable();

            $table->timestamps();

            // ── Index composites ─────────────────────────────────────────────
            $table->index(['tenant_id', 'quarter'],         'idx_vc_tenant_quarter');
            $table->index(['tenant_id', 'status'],          'idx_vc_tenant_status');
            $table->index(['user_id', 'quarter', 'status'], 'idx_vc_user_quarter_status');
            $table->index('status',                         'idx_vc_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('verification_codes');
    }
};
