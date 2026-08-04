<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateForcedPasswordRequest;
use Illuminate\Http\RedirectResponse;

class UpdateForcedPasswordController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(UpdateForcedPasswordRequest $request): RedirectResponse
    {
        $request->user()->forceFill([
            'password' => $request->validated('password'),
            'must_change_password' => false,
            'password_changed_at' => now(),
        ])->save();

        $request->session()->regenerate();

        return redirect()
            ->route('dashboard')
            ->with('status', 'Senha alterada com sucesso.');
    }
}
