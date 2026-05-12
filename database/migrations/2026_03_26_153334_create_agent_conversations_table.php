<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('agent_conversations')) {
            Schema::create('agent_conversations', function (Blueprint $table) {
                $table->string('id', 36)->primary();
                $table->uuid('tenant_id')->nullable()->index();
                $table->foreignId('user_id')->nullable();
                $table->string('title');
                $table->timestamps();
    
                $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
                $table->index(['user_id', 'updated_at']);
            });
        }

        if (!Schema::hasTable('agent_conversation_messages')) {
            Schema::create('agent_conversation_messages', function (Blueprint $table) {
                $table->string('id', 36)->primary();
                $table->uuid('tenant_id')->nullable()->index();
                $table->string('conversation_id', 36)->index();
                $table->foreignId('user_id')->nullable();
                $table->string('agent');
                $table->string('role', 25);
                $table->text('content');
                $table->text('attachments');
                $table->text('tool_calls');
                $table->text('tool_results');
                $table->text('usage');
                $table->text('meta');
                $table->timestamps();
    
                $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
                $table->index(['conversation_id', 'user_id', 'updated_at'], 'conversation_index');
                $table->index(['user_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('agent_conversation_messages');
        Schema::dropIfExists('agent_conversations');
    }
};
