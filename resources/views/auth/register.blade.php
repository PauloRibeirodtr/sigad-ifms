@extends('layouts.guest')

@section('title', 'Cadastro indisponível')

@section('content')
    <div class="text-center">
        <span class="mx-auto grid size-16 place-items-center rounded-2xl bg-slate-100 text-slate-500">
            <svg viewBox="0 0 24 24" class="size-8" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true">
                <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2M9 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8Z" stroke-linecap="round"/>
                <path d="m17 10 5 5m0-5-5 5" stroke-linecap="round"/>
            </svg>
        </span>
        <h1 class="mt-5 text-2xl font-extrabold tracking-tight text-slate-950">Cadastro público indisponível</h1>
        <p class="mt-2 text-sm leading-6 text-slate-500">Novas contas são criadas exclusivamente pela administração do sistema.</p>
        <a href="{{ route('login') }}" class="mt-6 inline-flex min-h-12 items-center justify-center rounded-xl bg-brand-700 px-5 text-sm font-bold text-white transition hover:bg-brand-800">Voltar para o login</a>
    </div>
@endsection
