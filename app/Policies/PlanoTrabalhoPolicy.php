<?php

namespace App\Policies;

use App\Models\PlanoTrabalho;
use App\Models\User;

class PlanoTrabalhoPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, PlanoTrabalho $planoTrabalho): bool
    {
        return $user->getKey() === $planoTrabalho->pit->user_id;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, PlanoTrabalho $planoTrabalho): bool
    {
        return $user->getKey() === $planoTrabalho->pit->user_id;
    }

    public function delete(User $user, PlanoTrabalho $planoTrabalho): bool
    {
        return false;
    }
}
