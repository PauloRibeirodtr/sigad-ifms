<?php

namespace Database\Factories;

use App\Enums\AtividadePrioridade;
use App\Enums\AtividadeStatus;
use App\Models\Atividade;
use App\Models\AtividadeCategoria;
use App\Models\AtividadeMovimentacao;
use App\Models\PlanoTrabalho;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Atividade>
 */
class AtividadeFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'plano_trabalho_id' => fn (array $attributes) => PlanoTrabalho::factory()->create([
                'user_id' => $attributes['user_id'],
                'data_inicial' => today()->subMonths(2),
                'data_final' => today()->addMonths(2),
            ])->id,
            'categoria_id' => fn (array $attributes) => AtividadeCategoria::factory()->create([
                'user_id' => $attributes['user_id'],
            ])->id,
            'titulo' => fake()->sentence(4),
            'descricao' => fake()->optional()->paragraph(),
            'solicitante' => fake()->optional()->name(),
            'data_atividade' => today(),
            'status' => AtividadeStatus::Aberta,
            'aguardando_por' => null,
            'aguardando_descricao' => null,
            'prioridade' => AtividadePrioridade::Normal,
            'prazo' => fake()->optional()->dateTimeBetween('now', '+2 months'),
            'proxima_acao' => fake()->optional()->sentence(),
        ];
    }

    public function configure(): static
    {
        return $this->afterCreating(function (Atividade $atividade): void {
            if ($atividade->movimentacoes()->doesntExist()) {
                AtividadeMovimentacao::factory()->for($atividade)->create([
                    'data_movimentacao' => $atividade->data_atividade,
                    'status' => $atividade->status,
                    'aguardando_por' => $atividade->aguardando_por,
                    'aguardando_descricao' => $atividade->aguardando_descricao,
                ]);
            }
        });
    }

    public function concluded(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => AtividadeStatus::Concluida,
        ]);
    }
}
