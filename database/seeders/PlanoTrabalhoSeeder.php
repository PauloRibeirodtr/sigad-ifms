<?php

namespace Database\Seeders;

use App\Models\Pit;
use App\Models\PlanoTrabalho;
use App\Models\User;
use Illuminate\Database\Seeder;

class PlanoTrabalhoSeeder extends Seeder
{
    public function run(): void
    {
        User::factory()
            ->count(3)
            ->create()
            ->each(fn (User $user) => Pit::factory()
                ->count(3)
                ->for($user)
                ->create()
                ->each(fn (Pit $pit) => PlanoTrabalho::factory()->count(2)->for($pit)->create()));
    }
}
