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
        Schema::table('atividade_movimentacoes', function (Blueprint $table) {
            $table->index(
                ['atividade_id', 'data_movimentacao', 'created_at', 'id'],
                'atividade_movimentacoes_ordem_negocio_idx',
            );
        });

        Schema::table('atividade_movimentacoes', function (Blueprint $table) {
            $table->dropIndex(['atividade_id', 'data_movimentacao']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('atividade_movimentacoes', function (Blueprint $table) {
            $table->index(['atividade_id', 'data_movimentacao']);
        });

        Schema::table('atividade_movimentacoes', function (Blueprint $table) {
            $table->dropIndex('atividade_movimentacoes_ordem_negocio_idx');
        });
    }
};
