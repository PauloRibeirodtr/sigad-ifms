<?php

namespace App\Http\Controllers;

use App\Actions\Relatorios\BuildPlanoTrabalhoReport;
use App\Models\Pit;
use App\Models\PlanoTrabalho;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class RelatorioPlanoTrabalhoController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', Pit::class);
        $pits = $request->user()->pits()
            ->with(['planosTrabalho' => fn (HasMany $plans) => $plans
                ->with('pit')
                ->withCount('atividades')
                ->orderBy('nome')])
            ->orderByDesc('ano')
            ->orderByDesc('semestre')
            ->orderByDesc('data_inicial')
            ->get();

        return view('reports.index', compact('pits'));
    }

    public function show(PlanoTrabalho $plano, BuildPlanoTrabalhoReport $buildReport): View
    {
        Gate::authorize('view', $plano);
        $plano->loadMissing('pit');

        $plano->load([
            'atividades' => fn (HasMany $query) => $query
                ->select([
                    'id',
                    'plano_trabalho_id',
                    'categoria_id',
                    'titulo',
                    'descricao',
                    'solicitante',
                    'data_atividade',
                    'status',
                    'prioridade',
                    'prazo',
                    'proxima_acao',
                ])
                ->with([
                    'categoria:id,nome',
                    'movimentacoes' => fn (HasMany $movements) => $movements->inBusinessOrder(),
                ])
                ->orderBy('data_atividade')
                ->orderBy('id'),
        ]);

        return view('reports.show', [
            'plan' => $plano,
            ...$buildReport->execute($plano),
        ]);
    }
}
