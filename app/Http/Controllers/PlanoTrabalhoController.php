<?php

namespace App\Http\Controllers;

use App\Actions\Atividades\GetAtividadeIndicatorCounts;
use App\Enums\AtividadeIndicador;
use App\Enums\AtividadeStatus;
use App\Http\Requests\StorePlanoTrabalhoRequest;
use App\Http\Requests\UpdatePlanoTrabalhoRequest;
use App\Models\PlanoTrabalho;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class PlanoTrabalhoController extends Controller
{
    public function index(Request $request, GetAtividadeIndicatorCounts $getIndicatorCounts): View
    {
        Gate::authorize('viewAny', PlanoTrabalho::class);
        $today = today()->toDateString();
        $ownedPlans = $request->user()->planosTrabalho();

        $summary = [
            'awaiting' => (clone $ownedPlans)->whereDate('data_inicial', '>', $today)->count(),
            'in_progress' => (clone $ownedPlans)
                ->whereDate('data_inicial', '<=', $today)
                ->whereDate('data_final', '>=', $today)
                ->count(),
            'ended' => (clone $ownedPlans)->whereDate('data_final', '<', $today)->count(),
            'pending_activities' => $request->user()->atividades()
                ->whereNotIn('status', [AtividadeStatus::Concluida->value, AtividadeStatus::Cancelada->value])
                ->count(),
            'concluded_activities' => $request->user()->atividades()
                ->where('status', AtividadeStatus::Concluida)
                ->count(),
        ];
        $activityIndicators = $getIndicatorCounts->execute($request->user()->atividades());

        $plans = $ownedPlans
            ->select(['id', 'user_id', 'nome', 'descricao', 'data_inicial', 'data_final', 'created_at', 'updated_at'])
            ->withCount([
                'atividades',
                'atividades as atividades_aguardando_count' => fn ($query) => $query->where('status', AtividadeStatus::Aguardando),
                'atividades as atividades_em_andamento_count' => fn ($query) => $query->where('status', AtividadeStatus::EmAndamento),
                'atividades as atividades_concluidas_count' => fn ($query) => $query->where('status', AtividadeStatus::Concluida),
                'atividades as atividades_atrasadas_count' => fn ($query) => $query->forIndicator(AtividadeIndicador::Atrasadas),
                'atividades as atividades_urgentes_count' => fn ($query) => $query->forIndicator(AtividadeIndicador::Urgentes),
                'atividades as atividades_sem_atualizacao_count' => fn ($query) => $query->forIndicator(AtividadeIndicador::SemAtualizacao),
            ])
            ->orderByDesc('data_inicial')
            ->paginate(6)
            ->withQueryString();

        return view('dashboard', [
            'plans' => $plans,
            'summary' => $summary,
            'activityIndicators' => $activityIndicators,
        ]);
    }

    public function create(): View
    {
        Gate::authorize('create', PlanoTrabalho::class);

        return view('plans.create');
    }

    public function store(StorePlanoTrabalhoRequest $request): RedirectResponse
    {
        $plan = $request->user()->planosTrabalho()->create($request->validated());

        return redirect()->route('plans.show', $plan)->with('status', 'Plano de Trabalho cadastrado com sucesso.');
    }

    public function show(PlanoTrabalho $plano): View
    {
        Gate::authorize('view', $plano);
        $plano->loadCount([
            'atividades',
            'atividades as atividades_aguardando_count' => fn ($query) => $query->where('status', AtividadeStatus::Aguardando),
            'atividades as atividades_em_andamento_count' => fn ($query) => $query->where('status', AtividadeStatus::EmAndamento),
            'atividades as atividades_concluidas_count' => fn ($query) => $query->where('status', AtividadeStatus::Concluida),
            'atividades as atividades_atrasadas_count' => fn ($query) => $query->forIndicator(AtividadeIndicador::Atrasadas),
            'atividades as atividades_urgentes_count' => fn ($query) => $query->forIndicator(AtividadeIndicador::Urgentes),
            'atividades as atividades_sem_atualizacao_count' => fn ($query) => $query->forIndicator(AtividadeIndicador::SemAtualizacao),
        ]);

        return view('plans.show', ['plan' => $plano]);
    }

    public function edit(PlanoTrabalho $plano): View
    {
        Gate::authorize('update', $plano);

        return view('plans.edit', ['plan' => $plano]);
    }

    public function update(UpdatePlanoTrabalhoRequest $request, PlanoTrabalho $plano): RedirectResponse
    {
        $plano->update($request->validated());

        return redirect()->route('plans.show', $plano)->with('status', 'Plano de Trabalho atualizado com sucesso.');
    }
}
