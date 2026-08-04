<?php

namespace App\Policies;

use App\Models\AtividadeCategoria;
use App\Models\User;

class AtividadeCategoriaPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, AtividadeCategoria $atividadeCategoria): bool
    {
        return $user->getKey() === $atividadeCategoria->user_id;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, AtividadeCategoria $atividadeCategoria): bool
    {
        return $user->getKey() === $atividadeCategoria->user_id;
    }

    public function activate(User $user, AtividadeCategoria $atividadeCategoria): bool
    {
        return $user->getKey() === $atividadeCategoria->user_id && ! $atividadeCategoria->ativa;
    }

    public function deactivate(User $user, AtividadeCategoria $atividadeCategoria): bool
    {
        return $user->getKey() === $atividadeCategoria->user_id && $atividadeCategoria->ativa;
    }

    public function delete(User $user, AtividadeCategoria $atividadeCategoria): bool
    {
        return false;
    }
}
