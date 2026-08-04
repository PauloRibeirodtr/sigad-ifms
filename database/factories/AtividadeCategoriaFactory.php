<?php

namespace Database\Factories;

use App\Models\AtividadeCategoria;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AtividadeCategoria>
 */
class AtividadeCategoriaFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'nome' => fake()->unique()->words(2, true),
            'descricao' => fake()->optional()->sentence(),
            'ativa' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'ativa' => false,
        ]);
    }
}
