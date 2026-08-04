<?php

namespace Database\Seeders;

use App\Models\Atividade;
use App\Models\AtividadeMovimentacao;
use Illuminate\Database\Seeder;

class AtividadeMovimentacaoSeeder extends Seeder
{
    public function run(): void
    {
        Atividade::query()->each(fn (Atividade $atividade) => AtividadeMovimentacao::factory()->for($atividade)->create([
            'data_movimentacao' => $atividade->data_atividade,
            'status' => $atividade->status,
        ]));
    }
}
