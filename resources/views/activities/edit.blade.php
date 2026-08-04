@extends('layouts.app')

@section('title', 'Editar atividade')
@section('page_title', 'Editar dados gerais')
@section('page_subtitle', $atividade->titulo)

@section('content')
    <div class="mx-auto max-w-5xl">
        <div class="mb-5"><a href="{{ route('plans.activities.show', [$plano, $atividade]) }}" class="inline-flex items-center gap-2 text-sm font-bold text-brand-700"><span>←</span> Voltar para a atividade</a></div>
        <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-card sm:p-8">
            <div class="mb-7 flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between"><div><p class="text-sm font-bold text-brand-700">Atividade #{{ $atividade->id }}</p><h2 class="mt-1 text-2xl font-extrabold text-slate-950">Dados gerais</h2><p class="mt-2 text-sm text-slate-500">O estado atual é alterado pelas movimentações, não por esta tela.</p></div><x-activity-status-badge :status="$atividade->status" /></div>
            <form method="POST" action="{{ route('plans.activities.update', [$plano, $atividade]) }}" class="grid gap-7">
                @csrf @method('PUT')
                <x-activity-general-fields :plan="$plano" :categories="$categories" :activity="$atividade" />
                <div class="flex flex-col-reverse gap-3 border-t border-slate-100 pt-6 sm:flex-row sm:justify-end"><a href="{{ route('plans.activities.show', [$plano, $atividade]) }}" class="inline-flex min-h-12 items-center justify-center rounded-xl border border-slate-200 px-5 text-sm font-bold text-slate-600">Cancelar</a><x-primary-button>Salvar alterações</x-primary-button></div>
            </form>
        </section>
    </div>
@endsection
