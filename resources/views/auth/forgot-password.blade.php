@extends('layouts.guest')

@section('title', 'Recuperar senha')

@section('content')
    <div class="mb-7">
        <a href="{{ route('login') }}" class="inline-flex items-center gap-2 text-sm font-bold text-brand-700 transition hover:text-brand-900">
            <svg viewBox="0 0 24 24" class="size-4" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path d="m15 18-6-6 6-6" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            Voltar para o login
        </a>
        <h1 class="mt-5 text-2xl font-extrabold tracking-tight text-slate-950">Recuperar senha</h1>
        <p class="mt-2 text-sm leading-6 text-slate-500">Informe seu e-mail para receber o link de redefinição.</p>
    </div>

    @if (session('status'))
        <x-alert class="mb-5">{{ session('status') }}</x-alert>
    @endif

    <form action="{{ route('password.email') }}" method="POST" class="grid gap-5">
        @csrf

        <x-form-input
            name="email"
            label="E-mail"
            type="email"
            placeholder="nome@instituicao.edu.br"
            autocomplete="email"
            required
            autofocus
        />

        <x-primary-button class="w-full">Enviar link de recuperação</x-primary-button>
    </form>
@endsection
