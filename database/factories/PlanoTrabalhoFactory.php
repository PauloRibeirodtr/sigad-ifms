<?php

namespace Database\Factories;

use App\Models\PlanoTrabalho;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PlanoTrabalho>
 */
class PlanoTrabalhoFactory extends Factory
{
    public function definition(): array
    {
        $startDate = fake()->dateTimeBetween('-6 months', '+3 months');
        $endDate = (clone $startDate)->modify('+'.fake()->numberBetween(30, 180).' days');

        return [
            'user_id' => User::factory(),
            'nome' => fake()->words(3, true),
            'descricao' => fake()->optional()->paragraph(),
            'data_inicial' => $startDate,
            'data_final' => $endDate,
        ];
    }

    public function awaiting(): static
    {
        return $this->state(fn (array $attributes) => [
            'data_inicial' => today()->addMonth(),
            'data_final' => today()->addMonths(4),
        ]);
    }

    public function inProgress(): static
    {
        return $this->state(fn (array $attributes) => [
            'data_inicial' => today()->subMonth(),
            'data_final' => today()->addMonth(),
        ]);
    }

    public function ended(): static
    {
        return $this->state(fn (array $attributes) => [
            'data_inicial' => today()->subMonths(4),
            'data_final' => today()->subMonth(),
        ]);
    }
}
