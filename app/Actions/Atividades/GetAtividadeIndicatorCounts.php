<?php

namespace App\Actions\Atividades;

use App\Enums\AtividadeIndicador;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;

class GetAtividadeIndicatorCounts
{
    /**
     * @param  Builder<\App\Models\Atividade>|Relation<\App\Models\Atividade, *, *>  $activities
     * @return array<string, int>
     */
    public function execute(Builder|Relation $activities): array
    {
        $counts = [];

        foreach (AtividadeIndicador::cases() as $indicator) {
            $counts[$indicator->value] = (clone $activities)->forIndicator($indicator)->count();
        }

        return $counts;
    }
}
