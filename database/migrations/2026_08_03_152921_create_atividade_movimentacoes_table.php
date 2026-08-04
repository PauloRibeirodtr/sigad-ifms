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
        Schema::create('atividade_movimentacoes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('atividade_id')->constrained('atividades')->restrictOnDelete();
            $table->date('data_movimentacao');
            $table->text('descricao');
            $table->string('status', 32);
            $table->string('aguardando_por', 32)->nullable();
            $table->string('aguardando_descricao')->nullable();
            $table->unsignedInteger('minutos_trabalhados')->nullable();
            $table->string('anexo_path')->nullable();
            $table->string('anexo_nome_original')->nullable();
            $table->timestamps();

            $table->index(['atividade_id', 'data_movimentacao']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('atividade_movimentacoes');
    }
};
