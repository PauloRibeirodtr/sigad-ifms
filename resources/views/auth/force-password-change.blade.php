@extends('layouts.guest')

@section('title', 'Alterar senha temporária')

@section('content')
    <div class="mb-7">
        <span class="inline-flex rounded-full bg-warning-50 px-3 py-1 text-xs font-bold uppercase tracking-wide text-warning-700">Ação obrigatória</span>
        <h1 class="mt-4 text-2xl font-extrabold tracking-tight text-slate-950">Crie uma nova senha</h1>
        <p class="mt-2 text-sm leading-6 text-slate-500">Você está usando uma senha temporária. Altere-a antes de acessar as demais áreas do sistema.</p>
    </div>

    <form action="{{ route('password.force.update') }}" method="POST" class="grid gap-5">
        @csrf
        @method('PUT')

        <x-form-input
            name="current_password"
            label="Senha temporária atual"
            type="password"
            autocomplete="current-password"
            required
            autofocus
        />

        <x-form-input
            name="password"
            label="Nova senha"
            type="password"
            autocomplete="new-password"
            required
        />

        <x-form-input
            name="password_confirmation"
            label="Confirmar nova senha"
            type="password"
            autocomplete="new-password"
            required
        />

        <div class="rounded-xl bg-slate-50 px-4 py-3 text-xs leading-5 text-slate-500">
            Use pelo menos 10 caracteres, com letras maiúsculas e minúsculas, número e símbolo.
        </div>

        <x-primary-button class="w-full">Salvar nova senha</x-primary-button>
    </form>

    <form action="{{ route('logout') }}" method="POST" class="mt-4 text-center">
        @csrf
        <button type="submit" class="text-sm font-bold text-slate-500 transition hover:text-slate-800 hover:underline">Sair do sistema</button>
    </form>
@endsection
