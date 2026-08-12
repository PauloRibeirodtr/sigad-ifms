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
        Schema::create('planos_trabalho', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pit_id')->constrained()->restrictOnDelete();
            $table->string('nome');
            $table->text('descricao')->nullable();
            $table->timestamps();

            $table->index(['pit_id', 'nome']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('planos_trabalho');
    }
};
