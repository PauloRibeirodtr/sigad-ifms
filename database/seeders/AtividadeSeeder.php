<?php

namespace Database\Seeders;

use App\Models\Atividade;
use Illuminate\Database\Seeder;

class AtividadeSeeder extends Seeder
{
    public function run(): void
    {
        Atividade::factory()->count(15)->create();
    }
}
