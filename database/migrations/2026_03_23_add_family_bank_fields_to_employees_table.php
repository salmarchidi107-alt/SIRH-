<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {

            if (!Schema::hasColumn('employees', 'children_count')) {
                $table->unsignedTinyInteger('children_count')
                      ->nullable()
                      ->default(0)
                      ->after('family_situation');
            }

            if (!Schema::hasColumn('employees', 'payment_method')) {
                $table->enum('payment_method', ['virement', 'cash', 'chèque'])
                      ->nullable()
                      ->after('children_count');
            }

            if (!Schema::hasColumn('employees', 'bank')) {
                $table->string('bank')
                      ->nullable()
                      ->after('payment_method');
            }

            if (!Schema::hasColumn('employees', 'rib')) {
                $table->string('rib')
                      ->nullable()
                      ->after('bank');
            }

            if (!Schema::hasColumn('employees', 'contractual_benefits')) {
                $table->text('contractual_benefits')
                      ->nullable()
                      ->after('rib');
            }
        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {

            $columns = [
                'children_count',
                'payment_method',
                'bank',
                'rib',
                'contractual_benefits'
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('employees', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};