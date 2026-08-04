<?php

namespace App\Policies;

use App\Enums\UserProfile;
use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdministrator();
    }

    public function view(User $user, User $model): bool
    {
        return $user->isAdministrator();
    }

    public function create(User $user): bool
    {
        return $user->isAdministrator();
    }

    public function update(User $user, User $model): bool
    {
        return $user->isAdministrator();
    }

    public function activate(User $user, User $model): bool
    {
        return $user->isAdministrator() && ! $model->ativo;
    }

    public function deactivate(User $user, User $model): bool
    {
        return $user->isAdministrator() && $user->isNot($model) && $model->ativo;
    }

    public function promote(User $user, User $model): bool
    {
        return $user->isAdministrator() && $model->perfil === UserProfile::Usuario;
    }

    public function demote(User $user, User $model): bool
    {
        return $user->isAdministrator()
            && $user->isNot($model)
            && $model->perfil === UserProfile::Administrador;
    }

    public function resetPassword(User $user, User $model): bool
    {
        return $user->isAdministrator() && $user->isNot($model);
    }
}
