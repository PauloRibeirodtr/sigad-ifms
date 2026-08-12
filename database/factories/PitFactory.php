<?php

namespace Database\Factories;

use App\Models\Pit;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Pit>
 */
class PitFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'ano' => fake()->numberBetween(2020, 2035),
            'semestre' => fake()->numberBetween(1, 2),
            'data_inicial' => $startDate = fake()->dateTimeBetween('-6 months', '+3 months'),
            'data_final' => (clone $startDate)->modify('+'.fake()->numberBetween(30, 180).' days'),
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
