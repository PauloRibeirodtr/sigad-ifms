<?php

namespace App\Http\Requests;

class UpdateAtividadeMovimentacaoRequest extends AtividadeMovimentacaoRequest
{
    public function authorize(): bool
    {
        $activity = $this->route('atividade');
        $movement = $this->route('movimentacao');

        return $activity->plano_trabalho_id === $this->route('plano')->getKey()
            && $movement->atividade_id === $activity->getKey()
            && $this->user()->can('update', $movement);
    }
}
