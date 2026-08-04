<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('atividades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->foreignId('plano_trabalho_id')->constrained('planos_trabalho')->restrictOnDelete();
            $table->foreignId('categoria_id')->constrained('atividade_categorias')->restrictOnDelete();
            $table->string('titulo');
            $table->text('descricao')->nullable();
            $table->string('solicitante')->nullable();
            $table->date('data_atividade');
            $table->string('status', 32);
            $table->string('aguardando_por', 32)->nullable();
            $table->string('aguardando_descricao')->nullable();
            $table->string('prioridade', 16)->default('normal');
            $table->date('prazo')->nullable();
            $table->text('proxima_acao')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['plano_trabalho_id', 'data_atividade']);
            $table->index(['plano_trabalho_id', 'prioridade']);
            $table->index('categoria_id');
            $table->index('prazo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('atividades');
    }
};
