<?php

namespace Database\Seeders;

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
            ->each(fn (User $user) => PlanoTrabalho::factory()->count(3)->for($user)->create());
    }
}
