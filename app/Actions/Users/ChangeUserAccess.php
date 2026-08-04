<?php

namespace App\Actions\Users;

use App\Enums\UserProfile;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ChangeUserAccess
{
    public function execute(
        User $actor,
        User $target,
        ?UserProfile $profile = null,
        ?bool $active = null,
    ): User {
        return DB::transaction(function () use ($actor, $target, $profile, $active): User {
            $removesAdministrativeAccess = $profile === UserProfile::Usuario || $active === false;
            $activeAdministratorCount = null;

            if ($removesAdministrativeAccess) {
                $activeAdministratorCount = User::query()
                    ->where('perfil', UserProfile::Administrador)
                    ->where('ativo', true)
                    ->lockForUpdate()
                    ->get(['id'])
                    ->count();
            }

            $lockedTarget = User::query()->lockForUpdate()->findOrFail($target->getKey());

            $willRemoveActiveAdministrator = $lockedTarget->isAdministrator()
                && $lockedTarget->ativo
                && $removesAdministrativeAccess;

            if ($actor->is($lockedTarget) && $willRemoveActiveAdministrator) {
                throw ValidationException::withMessages([
                    'user' => 'Você não pode desativar ou rebaixar sua própria conta.',
                ]);
            }

            if ($willRemoveActiveAdministrator && $activeAdministratorCount <= 1) {
                throw ValidationException::withMessages([
                    'user' => 'Não é possível desativar ou rebaixar o último administrador ativo do sistema.',
                ]);
            }

            if ($profile !== null) {
                $lockedTarget->perfil = $profile;
            }

            if ($active !== null) {
                $lockedTarget->ativo = $active;
            }

            $lockedTarget->save();

            return $lockedTarget;
        }, attempts: 3);
    }
}
