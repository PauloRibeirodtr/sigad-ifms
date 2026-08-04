<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Users\ChangeUserAccess;
use App\Actions\Users\InvalidateUserSessions;
use App\Enums\UserProfile;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class UserAccessController extends Controller
{
    public function activate(Request $request, User $user, ChangeUserAccess $changeAccess): RedirectResponse
    {
        Gate::authorize('activate', $user);
        $changeAccess->execute($request->user(), $user, active: true);

        return $this->redirectWithStatus('Usuário ativado com sucesso.');
    }

    public function deactivate(
        Request $request,
        User $user,
        ChangeUserAccess $changeAccess,
        InvalidateUserSessions $invalidateSessions,
    ): RedirectResponse {
        Gate::authorize('deactivate', $user);
        $updatedUser = $changeAccess->execute($request->user(), $user, active: false);
        $invalidateSessions->execute($updatedUser);

        return $this->redirectWithStatus('Usuário desativado com sucesso.');
    }

    public function promote(Request $request, User $user, ChangeUserAccess $changeAccess): RedirectResponse
    {
        Gate::authorize('promote', $user);
        $changeAccess->execute($request->user(), $user, profile: UserProfile::Administrador);

        return $this->redirectWithStatus('Usuário promovido a administrador.');
    }

    public function demote(Request $request, User $user, ChangeUserAccess $changeAccess): RedirectResponse
    {
        Gate::authorize('demote', $user);
        $changeAccess->execute($request->user(), $user, profile: UserProfile::Usuario);

        return $this->redirectWithStatus('Administrador rebaixado para usuário.');
    }

    private function redirectWithStatus(string $status): RedirectResponse
    {
        return redirect()->route('admin.users.index')->with('status', $status);
    }
}
