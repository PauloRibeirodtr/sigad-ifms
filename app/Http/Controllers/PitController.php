<?php

namespace App\Http\Controllers;

use App\Enums\AtividadeIndicador;
use App\Enums\AtividadeStatus;
use App\Http\Requests\StorePitRequest;
use App\Http\Requests\UpdatePitRequest;
use App\Models\Pit;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class PitController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', Pit::class);

        $pits = $request->user()->pits()
            ->with(['planosTrabalho' => fn ($plans) => $plans
                ->with('pit')
                ->withCount('atividades')
                ->orderBy('nome')])
            ->withCount('planosTrabalho')
            ->orderByDesc('ano')
            ->orderByDesc('semestre')
            ->orderByDesc('data_inicial')
            ->paginate(6);

        return view('dashboard', ['pits' => $pits]);
    }

    public function create(): View
    {
        Gate::authorize('create', Pit::class);

        return view('pits.create');
    }

    public function store(StorePitRequest $request): RedirectResponse
    {
        $pit = $request->user()->pits()->create($request->validated());

        return redirect()->route('pits.show', $pit)->with('status', 'PIT cadastrado com sucesso.');
    }

    public function show(Pit $pit): View
    {
        Gate::authorize('view', $pit);
        $pit->load(['planosTrabalho' => fn ($plans) => $plans
            ->with('pit')
            ->withCount([
                'atividades',
                'atividades as atividades_aguardando_count' => fn ($query) => $query->where('status', AtividadeStatus::Aguardando),
                'atividades as atividades_em_andamento_count' => fn ($query) => $query->where('status', AtividadeStatus::EmAndamento),
                'atividades as atividades_concluidas_count' => fn ($query) => $query->where('status', AtividadeStatus::Concluida),
                'atividades as atividades_atrasadas_count' => fn ($query) => $query->forIndicator(AtividadeIndicador::Atrasadas),
                'atividades as atividades_urgentes_count' => fn ($query) => $query->forIndicator(AtividadeIndicador::Urgentes),
                'atividades as atividades_sem_atualizacao_count' => fn ($query) => $query->forIndicator(AtividadeIndicador::SemAtualizacao),
            ])
            ->orderBy('nome')]);

        return view('pits.show', compact('pit'));
    }

    public function edit(Pit $pit): View
    {
        Gate::authorize('update', $pit);

        return view('pits.edit', compact('pit'));
    }

    public function update(UpdatePitRequest $request, Pit $pit): RedirectResponse
    {
        $pit->update($request->validated());

        return redirect()->route('pits.show', $pit)->with('status', 'PIT atualizado com sucesso.');
    }

    public function destroy(Pit $pit): RedirectResponse
    {
        Gate::authorize('delete', $pit);
        $pit->delete();

        return redirect()->route('pits.index')->with('status', 'PIT excluído com sucesso.');
    }
}
