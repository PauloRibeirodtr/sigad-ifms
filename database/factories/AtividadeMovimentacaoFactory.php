<?php

namespace Database\Factories;

use App\Enums\AtividadeStatus;
use App\Models\Atividade;
use App\Models\AtividadeMovimentacao;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AtividadeMovimentacao>
 */
class AtividadeMovimentacaoFactory extends Factory
{
    public function definition(): array
    {
        return [
            'atividade_id' => Atividade::factory(),
            'data_movimentacao' => today(),
            'descricao' => fake()->paragraph(),
            'status' => AtividadeStatus::Aberta,
            'aguardando_por' => null,
            'aguardando_descricao' => null,
            'minutos_trabalhados' => fake()->optional()->numberBetween(1, 480),
            'anexo_path' => null,
            'anexo_nome_original' => null,
        ];
    }
}
