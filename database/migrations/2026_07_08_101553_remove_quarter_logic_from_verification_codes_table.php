<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Vérifie si un index existe sur une table (portable, ne dépend pas
     * d'un driver précis grâce à SHOW INDEX / information_schema via Doctrine).
     */
    private function indexExists(string $table, string $indexName): bool
    {
        $conn = Schema::getConnection();

        $result = $conn->select(
            "SHOW INDEX FROM `{$table}` WHERE Key_name = ?",
            [$indexName]
        );

        return count($result) > 0;
    }

    public function up(): void
    {
        // 1. On crée D'ABORD le nouvel index (user_id, status), s'il n'existe pas déjà.
        //    MySQL a besoin qu'un index couvrant user_id existe en permanence pour la
        //    contrainte de clé étrangère.
        if (!$this->indexExists('verification_codes', 'idx_vc_user_status')) {
            Schema::table('verification_codes', function (Blueprint $table) {
                $table->index(['user_id', 'status'], 'idx_vc_user_status');
            });
        }

        // 2. On supprime les anciens index composites référencant "quarter",
        //    uniquement s'ils existent encore (idempotent : gère le cas où une
        //    tentative précédente a déjà supprimé l'un des deux).
        if ($this->indexExists('verification_codes', 'idx_vc_tenant_quarter')) {
            Schema::table('verification_codes', function (Blueprint $table) {
                $table->dropIndex('idx_vc_tenant_quarter');
            });
        }

        if ($this->indexExists('verification_codes', 'idx_vc_user_quarter_status')) {
            Schema::table('verification_codes', function (Blueprint $table) {
                $table->dropIndex('idx_vc_user_quarter_status');
            });
        }

        // 3. Avant de retirer 'expired' de l'enum, on convertit les lignes existantes
        //    en 'revoked' (équivalent le plus proche, l'expiration n'existant plus).
        //    Idempotent : ne touche que les lignes encore à 'expired'.
        DB::table('verification_codes')
            ->where('status', 'expired')
            ->update([
                'status'        => 'revoked',
                'revoked_at'    => DB::raw('COALESCE(revoked_at, expired_at, NOW())'),
                'revoke_reason' => DB::raw("COALESCE(NULLIF(revoke_reason, ''), 'Ancien code expiré (migration suppression trimestre)')"),
            ]);

        // On retire 'expired' de l'enum status (MySQL).
        DB::statement("ALTER TABLE verification_codes MODIFY COLUMN status ENUM('assigned','used','revoked') NOT NULL DEFAULT 'assigned'");

        // 4. On supprime les colonnes devenues inutiles, uniquement si présentes.
        Schema::table('verification_codes', function (Blueprint $table) {
            if (Schema::hasColumn('verification_codes', 'quarter')) {
                $table->dropColumn('quarter');
            }
            if (Schema::hasColumn('verification_codes', 'expired_at')) {
                $table->dropColumn('expired_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('verification_codes', function (Blueprint $table) {
            if (!Schema::hasColumn('verification_codes', 'quarter')) {
                $table->string('quarter', 10)->nullable()->after('tenant_id');
            }
            if (!Schema::hasColumn('verification_codes', 'expired_at')) {
                $table->timestamp('expired_at')->nullable()->after('revoke_reason');
            }
        });

        DB::statement("ALTER TABLE verification_codes MODIFY COLUMN status ENUM('assigned','used','revoked','expired') NOT NULL DEFAULT 'assigned'");

        if (!$this->indexExists('verification_codes', 'idx_vc_tenant_quarter')) {
            Schema::table('verification_codes', function (Blueprint $table) {
                $table->index(['tenant_id', 'quarter'], 'idx_vc_tenant_quarter');
            });
        }

        if (!$this->indexExists('verification_codes', 'idx_vc_user_quarter_status')) {
            Schema::table('verification_codes', function (Blueprint $table) {
                $table->index(['user_id', 'quarter', 'status'], 'idx_vc_user_quarter_status');
            });
        }

        if ($this->indexExists('verification_codes', 'idx_vc_user_status')) {
            Schema::table('verification_codes', function (Blueprint $table) {
                $table->dropIndex('idx_vc_user_status');
            });
        }
    }
};
