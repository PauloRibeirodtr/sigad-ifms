<?php

namespace App\Actions\Atividades;

use App\Enums\AtividadeStatus;
use App\Models\Atividade;
use App\Models\AtividadeCategoria;
use App\Models\PlanoTrabalho;
use App\Models\User;
use Illuminate\Support\Arr;

class CreateAtividade
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function execute(User $user, PlanoTrabalho $plan, array $data): Atividade
    {
        $category = AtividadeCategoria::query()
            ->whereBelongsTo($user)
            ->where('ativa', true)
            ->findOrFail($data['categoria_id']);

        $activity = new Atividade(Arr::only($data, [
            'titulo',
            'descricao',
            'solicitante',
            'data_atividade',
            'prioridade',
            'prazo',
            'proxima_acao',
        ]));
        $activity->status = AtividadeStatus::from($data['status']);
        $activity->user()->associate($user);
        $activity->planoTrabalho()->associate($plan);
        $activity->categoria()->associate($category);
        $activity->save();

        return $activity;
    }
}
