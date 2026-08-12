<?php

namespace App\Http\Controllers;

use App\Actions\Atividades\CreateAtividade;
use App\Actions\Atividades\GetAtividadeIndicatorCounts;
use App\Enums\AtividadeIndicador;
use App\Enums\AtividadePrioridade;
use App\Enums\AtividadeStatus;
use App\Http\Requests\StoreAtividadeRequest;
use App\Http\Requests\UpdateAtividadeRequest;
use App\Models\Atividade;
use App\Models\Pit;
use App\Models\PlanoTrabalho;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Gate;

class AtividadeController extends Controller
{
    public function overview(Request $request): View
    {
        Gate::authorize('viewAny', Atividade::class);
        $indicator = $request->enum('indicador', AtividadeIndicador::class);
        $search = $request->string('busca')->trim()->value();
        $matchingActivities = Atividade::query()
            ->whereBelongsTo($request->user())
            ->when($indicator, fn ($query, AtividadeIndicador $value) => $query->forIndicator($value))
            ->select('plano_trabalho_id');

        $plans = PlanoTrabalho::query()
            ->whereHas('pit', fn ($query) => $query->whereBelongsTo($request->user()))
            ->select(['id', 'pit_id', 'nome'])
            ->with('pit:id,user_id,ano,semestre,data_inicial,data_final')
            ->when($indicator, fn ($query) => $query->whereIn('id', $matchingActivities))
            ->when($search !== '', fn ($query) => $query->whereLike('nome', '%'.$search.'%'))
            ->withCount([
                'atividades',
                'atividades as atividades_atrasadas_count' => fn ($query) => $query->forIndicator(AtividadeIndicador::Atrasadas),
                'atividades as atividades_aguardando_count' => fn ($query) => $query->forIndicator(AtividadeIndicador::Aguardando),
                'atividades as atividades_urgentes_count' => fn ($query) => $query->forIndicator(AtividadeIndicador::Urgentes),
                'atividades as atividades_sem_atualizacao_count' => fn ($query) => $query->forIndicator(AtividadeIndicador::SemAtualizacao),
            ])
            ->orderByDesc(Pit::query()
                ->select('data_inicial')
                ->whereColumn('pits.id', 'planos_trabalho.pit_id'))
            ->paginate(9)
            ->withQueryString();

        return view('activities.overview', compact('plans', 'indicator'));
    }

    public function index(
        Request $request,
        PlanoTrabalho $plano,
        GetAtividadeIndicatorCounts $getIndicatorCounts,
    ): View {
        Gate::authorize('view', $plano);
        $status = $request->enum('status', AtividadeStatus::class);
        $priority = $request->enum('prioridade', AtividadePrioridade::class);
        $indicator = $request->enum('indicador', AtividadeIndicador::class);
        $search = $request->string('busca')->trim()->value();
        $indicatorCounts = $getIndicatorCounts->execute($plano->atividades());
        $matchingCategoryIds = $request->user()->atividadeCategorias()
            ->when($search !== '', fn ($query) => $query->whereLike('nome', '%'.$search.'%'))
            ->select('id');

        $activities = $plano->atividades()
            ->select([
                'id', 'user_id', 'plano_trabalho_id', 'categoria_id', 'titulo', 'solicitante',
                'data_atividade', 'status', 'aguardando_por', 'aguardando_descricao',
                'prioridade', 'prazo', 'updated_at',
            ])
            ->withLatestMovementDate()
            ->with('categoria:id,nome')
            ->when($indicator, fn ($query, AtividadeIndicador $value) => $query->forIndicator($value))
            ->when($status, fn ($query, AtividadeStatus $value) => $query->where('status', $value))
            ->when($priority, fn ($query, AtividadePrioridade $value) => $query->where('prioridade', $value))
            ->when($search !== '', fn ($query) => $query->where(fn ($searchQuery) => $searchQuery
                ->whereLike('titulo', '%'.$search.'%')
                ->orWhereLike('descricao', '%'.$search.'%')
                ->orWhereLike('solicitante', '%'.$search.'%')
                ->orWhereIn('categoria_id', $matchingCategoryIds)))
            ->when($request->filled('categoria_id'), fn ($query) => $query->where('categoria_id', $request->integer('categoria_id')))
            ->when($request->filled('titulo'), fn ($query) => $query->where('titulo', 'like', '%'.$request->string('titulo')->trim().'%'))
            ->when($request->filled('solicitante'), fn ($query) => $query->where('solicitante', 'like', '%'.$request->string('solicitante')->trim().'%'))
            ->when($request->filled('periodo_inicial'), fn ($query) => $query->whereDate('data_atividade', '>=', $request->string('periodo_inicial')->value()))
            ->when($request->filled('periodo_final'), fn ($query) => $query->whereDate('data_atividade', '<=', $request->string('periodo_final')->value()))
            ->when($request->string('prazo')->value() === 'atrasado', fn ($query) => $query
                ->whereDate('prazo', '<', today())
                ->whereNotIn('status', [AtividadeStatus::Concluida->value, AtividadeStatus::Cancelada->value]))
            ->when($request->string('prazo')->value() === 'com_prazo', fn ($query) => $query->whereNotNull('prazo'))
            ->when($request->string('prazo')->value() === 'sem_prazo', fn ($query) => $query->whereNull('prazo'))
            ->orderByRaw('CASE WHEN prioridade = ? THEN 0 WHEN prazo < ? AND status NOT IN (?, ?) THEN 1 WHEN status = ? THEN 2 WHEN status = ? THEN 3 WHEN status = ? THEN 4 WHEN status = ? THEN 5 ELSE 6 END', [
                AtividadePrioridade::Urgente->value,
                today()->toDateString(),
                AtividadeStatus::Concluida->value,
                AtividadeStatus::Cancelada->value,
                AtividadeStatus::Aguardando->value,
                AtividadeStatus::EmAndamento->value,
                AtividadeStatus::Aberta->value,
                AtividadeStatus::Concluida->value,
            ])
            ->orderByRaw('CASE prioridade WHEN ? THEN 0 WHEN ? THEN 1 WHEN ? THEN 2 ELSE 3 END', [
                AtividadePrioridade::Urgente->value,
                AtividadePrioridade::Alta->value,
                AtividadePrioridade::Normal->value,
            ])
            ->orderByDesc('data_atividade')
            ->paginate(10)
            ->withQueryString();

        $categories = $request->user()->atividadeCategorias()->orderBy('nome')->get(['id', 'nome', 'ativa']);

        return view('activities.index', compact(
            'plano',
            'activities',
            'categories',
            'indicator',
            'indicatorCounts',
        ));
    }

    public function create(Request $request, PlanoTrabalho $plano): View
    {
        Gate::authorize('view', $plano);
        Gate::authorize('create', Atividade::class);

        $categories = $request->user()->atividadeCategorias()->ativas()->orderBy('nome')->get(['id', 'nome']);

        return view('activities.create', compact('plano', 'categories'));
    }

    public function store(
        StoreAtividadeRequest $request,
        PlanoTrabalho $plano,
        CreateAtividade $createActivity,
    ): RedirectResponse {
        $activity = $createActivity->execute($request->user(), $plano, $request->validated());

        return redirect()->route('plans.activities.show', [$plano, $activity])
            ->with('status', 'Atividade cadastrada com sucesso.');
    }

    public function show(PlanoTrabalho $plano, Atividade $atividade): View
    {
        Gate::authorize('view', $atividade);
        $atividade->load([
            'categoria:id,nome,ativa',
            'movimentacoes' => fn ($query) => $query
                ->inBusinessOrder(),
        ]);
        $currentMovementId = $atividade->movimentacoes->last()?->getKey();

        return view('activities.show', compact('plano', 'atividade', 'currentMovementId'));
    }

    public function edit(Request $request, PlanoTrabalho $plano, Atividade $atividade): View
    {
        Gate::authorize('update', $atividade);

        $categories = $request->user()->atividadeCategorias()
            ->where(fn ($query) => $query->where('ativa', true)->orWhereKey($atividade->categoria_id))
            ->orderBy('nome')
            ->get(['id', 'nome', 'ativa']);

        return view('activities.edit', compact('plano', 'atividade', 'categories'));
    }

    public function update(
        UpdateAtividadeRequest $request,
        PlanoTrabalho $plano,
        Atividade $atividade,
    ): RedirectResponse {
        $data = $request->validated();
        $atividade->fill(Arr::except($data, 'categoria_id'));
        $atividade->categoria()->associate($data['categoria_id']);
        $atividade->save();

        return redirect()->route('plans.activities.show', [$plano, $atividade])
            ->with('status', 'Dados gerais da atividade atualizados com sucesso.');
    }
}
