@extends('layouts.guest')

@section('title', 'Redefinir senha')

@section('content')
    <div class="mb-7">
        <p class="text-sm font-bold text-brand-700">Segurança da conta</p>
        <h1 class="mt-1 text-2xl font-extrabold tracking-tight text-slate-950">Defina uma nova senha</h1>
        <p class="mt-2 text-sm leading-6 text-slate-500">Escolha uma senha segura e confirme-a para concluir.</p>
    </div>

    <form action="{{ route('password.update') }}" method="POST" class="grid gap-5">
        @csrf

        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <x-form-input
            name="email"
            label="E-mail"
            type="email"
            :value="$request->email"
            autocomplete="email"
            required
        />

        <x-form-input
            name="password"
            label="Nova senha"
            type="password"
            placeholder="Digite a nova senha"
            autocomplete="new-password"
            required
            autofocus
        />

        <x-form-input
            name="password_confirmation"
            label="Confirmar nova senha"
            type="password"
            placeholder="Repita a nova senha"
            autocomplete="new-password"
            required
        />

        <x-primary-button class="w-full">Redefinir senha</x-primary-button>
    </form>
@endsection
