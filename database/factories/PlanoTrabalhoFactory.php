<?php

namespace Database\Factories;

use App\Models\Pit;
use App\Models\PlanoTrabalho;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PlanoTrabalho>
 */
class PlanoTrabalhoFactory extends Factory
{
    public function definition(): array
    {
        return [
            'pit_id' => Pit::factory(),
            'nome' => fake()->words(3, true),
            'descricao' => fake()->optional()->paragraph(),
        ];
    }

    public function awaiting(): static
    {
        return $this->state(fn (array $attributes) => [
            'pit_id' => Pit::factory()->awaiting(),
        ]);
    }

    public function inProgress(): static
    {
        return $this->state(fn (array $attributes) => [
            'pit_id' => Pit::factory()->inProgress(),
        ]);
    }

    public function ended(): static
    {
        return $this->state(fn (array $attributes) => [
            'pit_id' => Pit::factory()->ended(),
        ]);
    }
}
