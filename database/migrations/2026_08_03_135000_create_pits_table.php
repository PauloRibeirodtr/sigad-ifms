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
        Schema::create('pits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->unsignedSmallInteger('ano');
            $table->unsignedTinyInteger('semestre');
            $table->date('data_inicial');
            $table->date('data_final');
            $table->timestamps();

            $table->index(['user_id', 'data_inicial']);
            $table->index(['user_id', 'data_final']);
            $table->index(['user_id', 'ano', 'semestre']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pits');
    }
};
