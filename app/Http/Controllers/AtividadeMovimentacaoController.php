<?php

namespace App\Http\Controllers;

use App\Actions\Movimentacoes\CreateAtividadeMovimentacao;
use App\Actions\Movimentacoes\UpdateAtividadeMovimentacao;
use App\Http\Requests\StoreAtividadeMovimentacaoRequest;
use App\Http\Requests\UpdateAtividadeMovimentacaoRequest;
use App\Models\Atividade;
use App\Models\AtividadeMovimentacao;
use App\Models\PlanoTrabalho;
use App\Support\MovementAttachmentStorage;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AtividadeMovimentacaoController extends Controller
{
    public function create(PlanoTrabalho $plano, Atividade $atividade): View
    {
        $this->ensureActivityBelongsToPlan($plano, $atividade);
        Gate::authorize('create', [AtividadeMovimentacao::class, $atividade]);

        return view('activities.movements.create', compact('plano', 'atividade'));
    }

    public function store(
        StoreAtividadeMovimentacaoRequest $request,
        PlanoTrabalho $plano,
        Atividade $atividade,
        CreateAtividadeMovimentacao $createMovement,
    ): RedirectResponse {
        $createMovement->execute($atividade, $request->validated());

        return redirect()->route('plans.activities.show', [$plano, $atividade])
            ->with('status', 'Movimentação cadastrada e estado da atividade atualizado.');
    }

    public function edit(
        PlanoTrabalho $plano,
        Atividade $atividade,
        AtividadeMovimentacao $movimentacao,
    ): View {
        $this->ensureMovementBelongsToActivity($plano, $atividade, $movimentacao);
        Gate::authorize('update', $movimentacao);

        return view('activities.movements.edit', compact('plano', 'atividade', 'movimentacao'));
    }

    public function update(
        UpdateAtividadeMovimentacaoRequest $request,
        PlanoTrabalho $plano,
        Atividade $atividade,
        AtividadeMovimentacao $movimentacao,
        UpdateAtividadeMovimentacao $updateMovement,
    ): RedirectResponse {
        $updateMovement->execute($atividade, $movimentacao, $request->validated());

        return redirect()->route('plans.activities.show', [$plano, $atividade])
            ->with('status', 'Movimentação atualizada e estado da atividade recalculado.');
    }

    public function download(
        PlanoTrabalho $plano,
        Atividade $atividade,
        AtividadeMovimentacao $movimentacao,
        MovementAttachmentStorage $attachmentStorage,
    ): StreamedResponse {
        $this->ensureMovementBelongsToActivity($plano, $atividade, $movimentacao);
        Gate::authorize('view', $movimentacao);
        abort_if($movimentacao->anexo_path === null || $movimentacao->anexo_nome_original === null, 404);

        return $attachmentStorage->download($movimentacao->anexo_path, $movimentacao->anexo_nome_original);
    }

    private function ensureActivityBelongsToPlan(PlanoTrabalho $plan, Atividade $activity): void
    {
        abort_unless($activity->plano_trabalho_id === $plan->getKey(), 404);
    }

    private function ensureMovementBelongsToActivity(
        PlanoTrabalho $plan,
        Atividade $activity,
        AtividadeMovimentacao $movement,
    ): void {
        $this->ensureActivityBelongsToPlan($plan, $activity);
        abort_unless($movement->atividade_id === $activity->getKey(), 404);
    }
}
