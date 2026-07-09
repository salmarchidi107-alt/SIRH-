<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('expenses')
            ->whereIn('status', ['brouillon', 'soumis'])
            ->update(['status' => 'valide']);
    }

    public function down(): void
    {
        // Non réversible de façon fiable : on ne sait plus quelles lignes
        // étaient "brouillon" vs "soumis" avant la conversion.
    }
};
