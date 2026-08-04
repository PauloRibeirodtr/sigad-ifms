<?php

namespace App\Http\Requests;

use App\Models\AtividadeMovimentacao;

class StoreAtividadeMovimentacaoRequest extends AtividadeMovimentacaoRequest
{
    public function authorize(): bool
    {
        $activity = $this->route('atividade');

        return $activity->plano_trabalho_id === $this->route('plano')->getKey()
            && $this->user()->can('create', [AtividadeMovimentacao::class, $activity]);
    }
}
