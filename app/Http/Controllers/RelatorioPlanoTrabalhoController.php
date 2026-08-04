<?php

namespace App\Http\Controllers;

use App\Actions\Relatorios\BuildPlanoTrabalhoReport;
use App\Http\Requests\RelatorioPlanoTrabalhoIndexRequest;
use App\Models\PlanoTrabalho;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Gate;

class RelatorioPlanoTrabalhoController extends Controller
{
    public function index(RelatorioPlanoTrabalhoIndexRequest $request): View
    {
        $plans = null;

        if ($request->filled(['data_inicial', 'data_final'])) {
            $dates = $request->validated();
            $exclusiveEndDate = CarbonImmutable::parse($dates['data_final'])->addDay()->toDateString();
            $plans = $request->user()->planosTrabalho()
                ->select(['id', 'user_id', 'nome', 'descricao', 'data_inicial', 'data_final'])
                ->where('data_final', '>=', $dates['data_inicial'])
                ->where('data_final', '<', $exclusiveEndDate)
                ->where('data_final', '<', today()->toDateString())
                ->withCount('atividades')
                ->orderByDesc('data_final')
                ->orderByDesc('id')
                ->paginate(9)
                ->withQueryString();
        }

        return view('reports.index', ['plans' => $plans]);
    }

    public function show(PlanoTrabalho $plano, BuildPlanoTrabalhoReport $buildReport): View
    {
        Gate::authorize('view', $plano);
        abort_unless($plano->data_final->lt(today()), 404);

        $plano->load([
            'atividades' => fn (HasMany $query) => $query
                ->select([
                    'id',
                    'user_id',
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
