<?php

namespace App\Actions\Movimentacoes;

use App\Models\Atividade;

class SyncAtividadeStatus
{
    public function execute(Atividade $activity): Atividade
    {
        $latestMovement = $activity->movimentacoes()
            ->inBusinessOrder(descending: true)
            ->firstOrFail();

        $activity->fill([
            'status' => $latestMovement->status,
            'aguardando_por' => $latestMovement->aguardando_por,
            'aguardando_descricao' => $latestMovement->aguardando_descricao,
        ])->save();

        return $activity;
    }
}
