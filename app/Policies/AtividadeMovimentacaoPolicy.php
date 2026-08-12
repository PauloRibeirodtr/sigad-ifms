<?php

namespace App\Policies;

use App\Models\Atividade;
use App\Models\AtividadeMovimentacao;
use App\Models\User;

class AtividadeMovimentacaoPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, AtividadeMovimentacao $movement): bool
    {
        return $movement->atividade()->whereBelongsTo($user)->exists()
            && $movement->atividade()->whereHas(
                'planoTrabalho.pit',
                fn ($query) => $query->whereBelongsTo($user),
            )->exists();
    }

    public function create(User $user, Atividade $activity): bool
    {
        return $user->can('update', $activity);
    }

    public function update(User $user, AtividadeMovimentacao $movement): bool
    {
        return $this->view($user, $movement);
    }

    public function delete(User $user, AtividadeMovimentacao $movement): bool
    {
        return false;
    }
}
