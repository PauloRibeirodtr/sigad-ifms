@extends('layouts.guest')

@section('title', 'Verificar e-mail')

@section('content')
    <div class="mb-7 text-center">
        <span class="mx-auto grid size-16 place-items-center rounded-2xl bg-brand-50 text-brand-700 ring-1 ring-brand-100">
            <svg viewBox="0 0 24 24" class="size-8" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true">
                <rect x="3" y="5" width="18" height="14" rx="3"/>
                <path d="m5 8 7 5 7-5" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </span>
        <h1 class="mt-5 text-2xl font-extrabold tracking-tight text-slate-950">Verifique seu e-mail</h1>
        <p class="mt-2 text-sm leading-6 text-slate-500">Enviamos um link de verificação. Acesse sua caixa de entrada para continuar.</p>
    </div>

    @if (session('status') === 'verification-link-sent')
        <x-alert class="mb-5">Um novo link de verificação foi enviado.</x-alert>
    @endif

    <form action="{{ route('verification.send') }}" method="POST">
        @csrf
        <x-primary-button class="w-full">Reenviar e-mail de verificação</x-primary-button>
    </form>

    <form action="{{ route('logout') }}" method="POST" class="mt-4 text-center">
        @csrf
        <button type="submit" class="text-sm font-bold text-slate-500 transition hover:text-slate-800 hover:underline">Sair do sistema</button>
    </form>
@endsection
