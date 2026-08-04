<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Users\GenerateTemporaryPassword;
use App\Actions\Users\InvalidateUserSessions;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class UserPasswordController extends Controller
{
    public function update(
        User $user,
        GenerateTemporaryPassword $passwordGenerator,
        InvalidateUserSessions $invalidateSessions,
    ): View {
        Gate::authorize('resetPassword', $user);
        $temporaryPassword = $passwordGenerator->execute();

        DB::transaction(function () use ($user, $temporaryPassword): void {
            $lockedUser = User::query()->lockForUpdate()->findOrFail($user->getKey());
            $lockedUser->update([
                'password' => $temporaryPassword,
                'must_change_password' => true,
                'password_changed_at' => null,
            ]);
        }, attempts: 3);

        $invalidateSessions->execute($user);

        return view('admin.users.temporary-password', [
            'user' => $user,
            'temporaryPassword' => $temporaryPassword,
            'reason' => 'reset',
        ]);
    }
}
