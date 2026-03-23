<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->unsignedTinyInteger('children_count')->nullable()->default(0)->after('family_situation');
            $table->enum('payment_method', ['virement', 'cash', 'chèque'])->nullable()->after('children_count');
            $table->string('bank')->nullable()->after('payment_method');
            $table->string('rib')->nullable()->after('bank');
            $table->text('contractual_benefits')->nullable()->after('rib');
        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn(['children_count', 'payment_method', 'bank', 'rib', 'contractual_benefits']);
        });
    }
};

