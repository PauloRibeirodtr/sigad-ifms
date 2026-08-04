@extends('layouts.app')

@section('title', 'Nova atividade')
@section('page_title', 'Nova atividade')
@section('page_subtitle', $plano->nome)

@section('content')
    <div class="mx-auto max-w-5xl">
        <div class="mb-5"><a href="{{ route('plans.activities.index', $plano) }}" class="inline-flex items-center gap-2 text-sm font-bold text-brand-700"><span>←</span> Voltar para atividades</a></div>

        @if ($categories->isEmpty())
            <x-alert type="error" class="mb-6">Você precisa cadastrar uma categoria ativa antes de criar uma atividade. <a href="{{ route('categories.create') }}" class="font-extrabold underline">Cadastrar categoria</a></x-alert>
        @endif

        <form method="POST" action="{{ route('plans.activities.store', $plano) }}" enctype="multipart/form-data" class="grid gap-6">
            @csrf
            <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-card sm:p-8">
                <div class="mb-7"><p class="text-sm font-bold text-brand-700">1. Dados gerais</p><h2 class="mt-1 text-2xl font-extrabold text-slate-950">Identificação da atividade</h2><p class="mt-2 text-sm text-slate-500">Período permitido: {{ $plano->data_inicial->format('d/m/Y') }} a {{ $plano->data_final->format('d/m/Y') }}.</p></div>
                <x-activity-general-fields :plan="$plano" :categories="$categories" />
            </section>

            <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-card sm:p-8">
                <div class="mb-7"><p class="text-sm font-bold text-brand-700">2. Primeira movimentação</p><h2 class="mt-1 text-2xl font-extrabold text-slate-950">Primeira ação realizada</h2><p class="mt-2 text-sm text-slate-500">A atividade e esta movimentação serão gravadas juntas, na mesma transação.</p></div>
                <x-first-movement-fields :plan="$plano" />
            </section>

            <div class="flex flex-col-reverse gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-card sm:flex-row sm:justify-end">
                <a href="{{ route('plans.activities.index', $plano) }}" class="inline-flex min-h-12 items-center justify-center rounded-xl border border-slate-200 px-5 text-sm font-bold text-slate-600">Cancelar</a>
                <x-primary-button :disabled="$categories->isEmpty()">Cadastrar atividade</x-primary-button>
            </div>
        </form>
    </div>
@endsection
