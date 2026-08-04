<?php

namespace Database\Seeders;

use App\Models\AtividadeCategoria;
use App\Models\User;
use Illuminate\Database\Seeder;

class AtividadeCategoriaSeeder extends Seeder
{
    public function run(): void
    {
        User::factory()
            ->count(3)
            ->create()
            ->each(fn (User $user) => AtividadeCategoria::factory()->count(5)->for($user)->create());
    }
}
