<?php

namespace App\Policies;

use App\Models\Atividade;
use App\Models\User;

class AtividadePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Atividade $atividade): bool
    {
        return $user->getKey() === $atividade->user_id
            && $atividade->planoTrabalho()->whereHas(
                'pit',
                fn ($query) => $query->whereBelongsTo($user),
            )->exists();
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Atividade $atividade): bool
    {
        return $this->view($user, $atividade);
    }

    public function delete(User $user, Atividade $atividade): bool
    {
        return false;
    }
}
