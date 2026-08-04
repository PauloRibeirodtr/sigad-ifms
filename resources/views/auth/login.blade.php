@extends('layouts.guest')

@section('title', 'Entrar')

@section('content')
    <div class="mb-7">
        <p class="text-sm font-bold text-brand-700">Bem-vindo</p>
        <h1 class="mt-1 text-2xl font-extrabold tracking-tight text-slate-950">Acesse sua conta</h1>
        <p class="mt-2 text-sm leading-6 text-slate-500">Use seu e-mail institucional e sua senha para continuar.</p>
    </div>

    @if (session('status'))
        <x-alert class="mb-5">{{ session('status') }}</x-alert>
    @endif

    <form action="{{ route('login') }}" method="POST" class="grid gap-5">
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

        <x-form-input
            name="password"
            label="Senha"
            type="password"
            placeholder="Digite sua senha"
            autocomplete="current-password"
            required
        />

        <div class="flex items-center justify-between gap-4">
            <label class="flex cursor-pointer items-center gap-2 text-sm font-medium text-slate-600">
                <input type="checkbox" name="remember" value="1" class="size-4 rounded border-slate-300 text-brand-700 focus:ring-brand-500">
                Lembrar-me
            </label>

            <a href="{{ route('password.request') }}" class="text-sm font-bold text-brand-700 transition hover:text-brand-900 hover:underline">
                Esqueci minha senha
            </a>
        </div>

        <x-primary-button class="mt-1 w-full">
            Entrar
            <svg viewBox="0 0 24 24" class="size-5" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path d="M5 12h14m-5-5 5 5-5 5" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </x-primary-button>
    </form>
@endsection
