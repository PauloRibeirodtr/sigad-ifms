@extends('layouts.app')

@section('title', 'Usuários')
@section('page_title', 'Administração de usuários')
@section('page_subtitle', 'Gerencie as contas institucionais e seus acessos')

@section('content')
    <div class="grid gap-6">
        @error('user')
            <x-alert type="error">{{ $message }}</x-alert>
        @enderror

        <section class="rounded-3xl border border-slate-200 bg-white p-5 shadow-card sm:p-6">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <p class="text-sm font-bold text-brand-700">Contas cadastradas</p>
                    <h2 class="mt-1 text-2xl font-extrabold tracking-tight text-slate-950">{{ $users->total() }} {{ $users->total() === 1 ? 'usuário' : 'usuários' }}</h2>
                </div>
                <a href="{{ route('admin.users.create') }}" class="inline-flex min-h-12 items-center justify-center gap-2 rounded-xl bg-brand-700 px-5 text-sm font-bold text-white shadow-sm transition hover:bg-brand-800">
                    <span class="text-xl leading-none" aria-hidden="true">+</span>
                    Novo usuário
                </a>
            </div>

            <form method="GET" action="{{ route('admin.users.index') }}" class="mt-6 grid gap-3 border-t border-slate-100 pt-6 sm:grid-cols-2 xl:grid-cols-5">
                <x-form-input name="nome" label="Nome" :value="request('nome')" placeholder="Buscar por nome" />
                <x-form-input name="email" label="E-mail" type="email" :value="request('email')" placeholder="Buscar por e-mail" />

                <div class="grid gap-2">
                    <label for="perfil" class="text-sm font-semibold text-slate-700">Perfil</label>
                    <select id="perfil" name="perfil" class="min-h-12 rounded-xl border border-slate-200 bg-white px-4 text-sm text-slate-900 shadow-sm focus:border-brand-500 focus:ring-4 focus:ring-brand-100">
                        <option value="">Todos</option>
                        @foreach (\App\Enums\UserProfile::cases() as $profile)
                            <option value="{{ $profile->value }}" @selected(request('perfil') === $profile->value)>{{ $profile->label() }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="grid gap-2">
                    <label for="status" class="text-sm font-semibold text-slate-700">Status</label>
                    <select id="status" name="status" class="min-h-12 rounded-xl border border-slate-200 bg-white px-4 text-sm text-slate-900 shadow-sm focus:border-brand-500 focus:ring-4 focus:ring-brand-100">
                        <option value="">Todos</option>
                        <option value="ativo" @selected(request('status') === 'ativo')>Ativo</option>
                        <option value="inativo" @selected(request('status') === 'inativo')>Inativo</option>
                    </select>
                </div>

                <div class="flex items-end gap-2">
                    <button type="submit" class="inline-flex min-h-12 flex-1 items-center justify-center rounded-xl bg-brand-700 px-4 text-sm font-bold text-white transition hover:bg-brand-800">Filtrar</button>
                    <a href="{{ route('admin.users.index') }}" class="inline-flex min-h-12 items-center justify-center rounded-xl border border-slate-200 px-4 text-sm font-bold text-slate-600 transition hover:bg-slate-50" aria-label="Limpar filtros">Limpar</a>
                </div>
            </form>
        </section>

        <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-card">
            @if ($users->isEmpty())
                <div class="px-6 py-16 text-center">
                    <div class="mx-auto grid size-14 place-items-center rounded-2xl bg-slate-100 text-2xl" aria-hidden="true">⌕</div>
                    <h3 class="mt-4 text-lg font-extrabold text-slate-900">Nenhum usuário encontrado</h3>
                    <p class="mt-1 text-sm text-slate-500">Revise os filtros ou cadastre uma nova conta.</p>
                </div>
            @else
                <div class="hidden overflow-x-auto xl:block">
                    <table class="w-full min-w-[1120px] text-left text-sm">
                        <thead class="border-b border-slate-200 bg-slate-50 text-xs font-extrabold uppercase tracking-wide text-slate-500">
                            <tr>
                                <th class="px-5 py-4">Usuário</th>
                                <th class="px-4 py-4">Perfil</th>
                                <th class="px-4 py-4">Status</th>
                                <th class="px-4 py-4">Senha</th>
                                <th class="px-4 py-4">Cadastro</th>
                                <th class="px-4 py-4">Última alteração</th>
                                <th class="px-5 py-4 text-right">Ações</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($users as $user)
                                <tr class="align-top transition hover:bg-slate-50/70">
                                    <td class="px-5 py-5">
                                        <p class="font-bold text-slate-900">{{ $user->name }}</p>
                                        <p class="mt-1 text-xs text-slate-500">{{ $user->email }}</p>
                                        @if (auth()->user()->is($user))
                                            <span class="mt-2 inline-flex rounded-full bg-sky-50 px-2 py-1 text-[0.65rem] font-extrabold uppercase text-sky-700">Sua conta</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-5">
                                        <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-bold {{ $user->isAdministrator() ? 'bg-brand-100 text-brand-800' : 'bg-slate-100 text-slate-700' }}">{{ $user->perfil->label() }}</span>
                                    </td>
                                    <td class="px-4 py-5">
                                        <span class="inline-flex items-center gap-1.5 text-xs font-bold {{ $user->ativo ? 'text-brand-700' : 'text-red-700' }}">
                                            <span class="size-2 rounded-full {{ $user->ativo ? 'bg-brand-500' : 'bg-red-500' }}"></span>
                                            {{ $user->ativo ? 'Ativo' : 'Inativo' }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-5 text-xs text-slate-600">{{ $user->must_change_password ? 'Troca obrigatória' : 'Definida' }}</td>
                                    <td class="px-4 py-5 text-xs text-slate-600">{{ $user->created_at->format('d/m/Y H:i') }}</td>
                                    <td class="px-4 py-5 text-xs text-slate-600">{{ $user->updated_at->format('d/m/Y H:i') }}</td>
                                    <td class="px-5 py-5">
                                        <div class="flex flex-wrap justify-end gap-2">
                                            <a href="{{ route('admin.users.edit', $user) }}" class="inline-flex min-h-9 items-center rounded-lg border border-slate-200 px-3 text-xs font-bold text-slate-700 transition hover:bg-slate-100">Editar</a>
                                            <x-admin.user-actions :user="$user" />
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="divide-y divide-slate-100 xl:hidden">
                    @foreach ($users as $user)
                        <article class="grid gap-4 p-5 sm:p-6">
                            <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                                <div>
                                    <div class="flex flex-wrap items-center gap-2">
                                        <h3 class="font-extrabold text-slate-950">{{ $user->name }}</h3>
                                        @if (auth()->user()->is($user))
                                            <span class="rounded-full bg-sky-50 px-2 py-1 text-[0.65rem] font-extrabold uppercase text-sky-700">Sua conta</span>
                                        @endif
                                    </div>
                                    <p class="mt-1 break-all text-sm text-slate-500">{{ $user->email }}</p>
                                </div>
                                <div class="flex flex-wrap gap-2">
                                    <span class="rounded-full px-2.5 py-1 text-xs font-bold {{ $user->isAdministrator() ? 'bg-brand-100 text-brand-800' : 'bg-slate-100 text-slate-700' }}">{{ $user->perfil->label() }}</span>
                                    <span class="rounded-full px-2.5 py-1 text-xs font-bold {{ $user->ativo ? 'bg-brand-50 text-brand-700' : 'bg-red-50 text-red-700' }}">{{ $user->ativo ? 'Ativo' : 'Inativo' }}</span>
                                </div>
                            </div>

                            <dl class="grid gap-3 rounded-2xl bg-slate-50 p-4 text-xs sm:grid-cols-3">
                                <div><dt class="font-bold text-slate-500">Senha</dt><dd class="mt-1 text-slate-800">{{ $user->must_change_password ? 'Troca obrigatória' : 'Definida' }}</dd></div>
                                <div><dt class="font-bold text-slate-500">Cadastro</dt><dd class="mt-1 text-slate-800">{{ $user->created_at->format('d/m/Y H:i') }}</dd></div>
                                <div><dt class="font-bold text-slate-500">Atualização</dt><dd class="mt-1 text-slate-800">{{ $user->updated_at->format('d/m/Y H:i') }}</dd></div>
                            </dl>

                            <div class="flex flex-wrap gap-2">
                                <a href="{{ route('admin.users.edit', $user) }}" class="inline-flex min-h-10 items-center rounded-lg border border-slate-200 px-3 text-xs font-bold text-slate-700 transition hover:bg-slate-100">Editar</a>
                                <x-admin.user-actions :user="$user" />
                            </div>
                        </article>
                    @endforeach
                </div>

                @if ($users->hasPages())
                    <div class="border-t border-slate-200 px-5 py-5 sm:px-6">{{ $users->links() }}</div>
                @endif
            @endif
        </section>
    </div>
@endsection
