<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Users\GenerateTemporaryPassword;
use App\Enums\UserProfile;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', User::class);
        $status = $request->string('status')->value();

        $users = User::query()
            ->when($request->filled('nome'), fn ($query) => $query->where('name', 'like', '%'.$request->string('nome')->trim().'%'))
            ->when($request->filled('email'), fn ($query) => $query->where('email', 'like', '%'.$request->string('email')->trim().'%'))
            ->when($request->enum('perfil', UserProfile::class), fn ($query, UserProfile $profile) => $query->where('perfil', $profile))
            ->when(in_array($status, ['ativo', 'inativo'], true), fn ($query) => $query->where('ativo', $status === 'ativo'))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.users.index', ['users' => $users]);
    }

    public function create(): View
    {
        Gate::authorize('create', User::class);

        return view('admin.users.create');
    }

    public function store(StoreUserRequest $request, GenerateTemporaryPassword $passwordGenerator): View
    {
        $temporaryPassword = $passwordGenerator->execute();
        $validated = $request->validated();

        $user = User::query()->create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'perfil' => $validated['perfil'],
            'ativo' => $validated['ativo'],
            'password' => $temporaryPassword,
            'must_change_password' => true,
            'password_changed_at' => null,
            'email_verified_at' => now(),
        ]);

        return view('admin.users.temporary-password', [
            'user' => $user,
            'temporaryPassword' => $temporaryPassword,
            'reason' => 'created',
        ]);
    }

    public function edit(User $user): View
    {
        Gate::authorize('update', $user);

        return view('admin.users.edit', ['user' => $user]);
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $user->update($request->validated());

        return redirect()->route('admin.users.index')->with('status', 'Usuário atualizado com sucesso.');
    }
}
