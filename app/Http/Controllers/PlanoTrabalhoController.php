<?php

namespace App\Http\Controllers;

use App\Enums\AtividadeIndicador;
use App\Enums\AtividadeStatus;
use App\Http\Requests\StorePlanoTrabalhoRequest;
use App\Http\Requests\UpdatePlanoTrabalhoRequest;
use App\Models\Pit;
use App\Models\PlanoTrabalho;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class PlanoTrabalhoController extends Controller
{
    public function create(Pit $pit): View
    {
        Gate::authorize('create', PlanoTrabalho::class);
        Gate::authorize('view', $pit);

        return view('plans.create', compact('pit'));
    }

    public function store(StorePlanoTrabalhoRequest $request, Pit $pit): RedirectResponse
    {
        $plan = $pit->planosTrabalho()->create($request->validated());

        return redirect()->route('plans.show', $plan)->with('status', 'PAT cadastrado com sucesso.');
    }

    public function show(PlanoTrabalho $plano): View
    {
        Gate::authorize('view', $plano);
        $plano->load('pit')->loadCount([
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
        $plano->loadMissing('pit');

        return view('plans.edit', ['plan' => $plano]);
    }

    public function update(UpdatePlanoTrabalhoRequest $request, PlanoTrabalho $plano): RedirectResponse
    {
        $plano->update($request->validated());

        return redirect()->route('plans.show', $plano)->with('status', 'PAT atualizado com sucesso.');
    }
}
