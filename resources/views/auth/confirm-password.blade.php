@extends('layouts.guest')

@section('title', 'Confirmar senha')

@section('content')
    <div class="mb-7">
        <p class="text-sm font-bold text-brand-700">Confirmação de segurança</p>
        <h1 class="mt-1 text-2xl font-extrabold tracking-tight text-slate-950">Confirme sua senha</h1>
        <p class="mt-2 text-sm leading-6 text-slate-500">Esta é uma área protegida. Confirme sua senha antes de continuar.</p>
    </div>

    <form action="{{ route('password.confirm.store') }}" method="POST" class="grid gap-5">
        @csrf

        <x-form-input
            name="password"
            label="Senha atual"
            type="password"
            placeholder="Digite sua senha"
            autocomplete="current-password"
            required
            autofocus
        />

        <x-primary-button class="w-full">Confirmar e continuar</x-primary-button>
    </form>
@endsection
