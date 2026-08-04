<?php

namespace App\Actions\Relatorios;

use App\Enums\AtividadeStatus;
use App\Models\Atividade;
use App\Models\PlanoTrabalho;
use Illuminate\Support\Collection;

class BuildPlanoTrabalhoReport
{
    /**
     * @return array{
     *     atividades: Collection<int, \App\Models\Atividade>,
     *     categorias: Collection<int, array{nome: string, atividades: int, movimentacoes: int, minutos: int}>,
     *     resumo: array{atividades: int, movimentacoes: int, minutos: int, sem_tempo: int, concluidas: int, canceladas: int},
     *     pendencias: Collection<int, \App\Models\Atividade>
     * }
     */
    public function execute(PlanoTrabalho $plan): array
    {
        $activities = $plan->atividades;
        $movements = $activities->flatMap->movimentacoes;

        $categories = $activities
            ->groupBy(fn (Atividade $activity): string => $activity->categoria->nome)
            ->map(fn (Collection $categoryActivities, string $name): array => [
                'nome' => $name,
                'atividades' => $categoryActivities->count(),
                'movimentacoes' => $categoryActivities->sum(fn (Atividade $activity): int => $activity->movimentacoes->count()),
                'minutos' => $categoryActivities->sum(fn (Atividade $activity): int => $activity->movimentacoes->sum('minutos_trabalhados')),
            ])
            ->sortBy('nome', SORT_NATURAL | SORT_FLAG_CASE)
            ->values();

        return [
            'atividades' => $activities,
            'categorias' => $categories,
            'resumo' => [
                'atividades' => $activities->count(),
                'movimentacoes' => $movements->count(),
                'minutos' => $movements->sum('minutos_trabalhados'),
                'sem_tempo' => $movements->whereNull('minutos_trabalhados')->count(),
                'concluidas' => $activities->where('status', AtividadeStatus::Concluida)->count(),
                'canceladas' => $activities->where('status', AtividadeStatus::Cancelada)->count(),
            ],
            'pendencias' => $activities->reject(fn (Atividade $activity): bool => in_array(
                $activity->status,
                [AtividadeStatus::Concluida, AtividadeStatus::Cancelada],
                true,
            )),
        ];
    }
}
