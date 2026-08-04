@extends('layouts.app')

@section('title', 'Planos de Trabalho')
@section('page_title', request()->routeIs('dashboard') ? 'Painel inicial' : 'Planos de Trabalho')
@section('page_subtitle', 'Visão geral dos seus Planos de Trabalho')

@section('content')
    <section aria-labelledby="resumo-heading">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-sm font-bold text-brand-700">Olá, {{ auth()->user()->name }}</p>
                <h2 id="resumo-heading" class="mt-1 text-2xl font-extrabold tracking-tight text-slate-950">Resumo dos seus planos</h2>
            </div>
            <a href="{{ route('plans.create') }}" class="inline-flex min-h-12 items-center justify-center gap-2 rounded-xl bg-brand-700 px-5 text-sm font-bold text-white shadow-sm transition hover:bg-brand-800">
                <span class="text-xl leading-none" aria-hidden="true">+</span>
                Novo Plano de Trabalho
            </a>
        </div>

        <div class="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <x-metric-card label="Planos em andamento" :value="$summary['in_progress']">
                <x-slot:icon><svg viewBox="0 0 24 24" class="size-6" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M4 5.5A2.5 2.5 0 0 1 6.5 3H10l2 2h5.5A2.5 2.5 0 0 1 20 7.5v10a2.5 2.5 0 0 1-2.5 2.5h-11A2.5 2.5 0 0 1 4 17.5v-12Z" stroke-linejoin="round"/></svg></x-slot:icon>
            </x-metric-card>
            <x-metric-card label="Planos encerrados" :value="$summary['ended']" tone="slate">
                <x-slot:icon><svg viewBox="0 0 24 24" class="size-6" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="m8 12 2.5 2.5L16 9" stroke-linecap="round" stroke-linejoin="round"/></svg></x-slot:icon>
            </x-metric-card>
            <x-metric-card label="Atividades pendentes" :value="$summary['pending_activities']" tone="warning">
                <x-slot:icon><svg viewBox="0 0 24 24" class="size-6" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2" stroke-linecap="round" stroke-linejoin="round"/></svg></x-slot:icon>
            </x-metric-card>
            <x-metric-card label="Atividades concluídas" :value="$summary['concluded_activities']">
                <x-slot:icon><svg viewBox="0 0 24 24" class="size-6" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="m8 12 2.5 2.5L16 9" stroke-linecap="round" stroke-linejoin="round"/></svg></x-slot:icon>
            </x-metric-card>
        </div>
    </section>

    <section class="mt-8" aria-labelledby="acompanhamento-heading">
        <div>
            <p class="text-sm font-bold text-brand-700">Acompanhamento operacional</p>
            <h2 id="acompanhamento-heading" class="mt-1 text-xl font-extrabold tracking-tight text-slate-950">Atividades que precisam de atenção</h2>
            <p class="mt-1 text-sm text-slate-500">Selecione um indicador para localizar os Planos de Trabalho correspondentes.</p>
        </div>

        <div class="mt-5 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <x-metric-card label="Atividades atrasadas" :value="$activityIndicators['atrasadas']" tone="danger" :href="route('activities.overview', ['indicador' => 'atrasadas'])">
                <x-slot:icon><svg viewBox="0 0 24 24" class="size-6" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M12 8v5m0 3h.01" stroke-linecap="round"/><path d="M10.3 3.8 2.7 17a2 2 0 0 0 1.7 3h15.2a2 2 0 0 0 1.7-3L13.7 3.8a2 2 0 0 0-3.4 0Z" stroke-linejoin="round"/></svg></x-slot:icon>
            </x-metric-card>
            <x-metric-card label="Aguardando retorno" :value="$activityIndicators['aguardando']" tone="warning" :href="route('activities.overview', ['indicador' => 'aguardando'])">
                <x-slot:icon><svg viewBox="0 0 24 24" class="size-6" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2" stroke-linecap="round" stroke-linejoin="round"/></svg></x-slot:icon>
            </x-metric-card>
            <x-metric-card label="Prioridade urgente" :value="$activityIndicators['urgentes']" :href="route('activities.overview', ['indicador' => 'urgentes'])">
                <x-slot:icon><svg viewBox="0 0 24 24" class="size-6" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="m13 2-8 12h7l-1 8 8-12h-7l1-8Z" stroke-linejoin="round"/></svg></x-slot:icon>
            </x-metric-card>
            <x-metric-card label="Sem atualização recente" :value="$activityIndicators['sem_atualizacao']" tone="slate" :href="route('activities.overview', ['indicador' => 'sem_atualizacao'])">
                <x-slot:icon><svg viewBox="0 0 24 24" class="size-6" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M12 6v6l4 2" stroke-linecap="round"/><path d="M4.9 5.1A9 9 0 1 1 3 12" stroke-linecap="round"/><path d="M3 4v5h5" stroke-linecap="round" stroke-linejoin="round"/></svg></x-slot:icon>
            </x-metric-card>
        </div>
    </section>

    <section class="mt-8" aria-labelledby="planos-heading">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h2 id="planos-heading" class="text-xl font-extrabold tracking-tight text-slate-950">Meus Planos de Trabalho</h2>
                <p class="mt-1 text-sm text-slate-500">{{ $plans->total() }} {{ $plans->total() === 1 ? 'plano cadastrado' : 'planos cadastrados' }} · {{ $summary['awaiting'] }} aguardando início.</p>
            </div>
        </div>

        @if ($plans->isEmpty())
            <div class="mt-5 overflow-hidden rounded-3xl border border-dashed border-brand-300 bg-white shadow-card">
                <div class="grid min-h-80 place-items-center px-6 py-12 text-center">
                    <div class="max-w-md">
                        <span class="mx-auto grid size-20 place-items-center rounded-3xl bg-brand-50 text-brand-700 ring-1 ring-brand-100">
                            <svg viewBox="0 0 24 24" class="size-10" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><path d="M4 5.5A2.5 2.5 0 0 1 6.5 3H10l2 2h5.5A2.5 2.5 0 0 1 20 7.5v10a2.5 2.5 0 0 1-2.5 2.5h-11A2.5 2.5 0 0 1 4 17.5v-12Z" stroke-linejoin="round"/><path d="M12 10v6m-3-3h6" stroke-linecap="round"/></svg>
                        </span>
                        <h3 class="mt-6 text-xl font-extrabold text-slate-900">Nenhum Plano de Trabalho cadastrado</h3>
                        <p class="mt-2 text-sm leading-6 text-slate-500">Cadastre o primeiro plano para organizar suas atividades dentro de um período definido.</p>
                        <a href="{{ route('plans.create') }}" class="mt-6 inline-flex min-h-11 items-center justify-center rounded-xl bg-brand-700 px-5 text-sm font-bold text-white transition hover:bg-brand-800">Novo Plano de Trabalho</a>
                    </div>
                </div>
            </div>
        @else
            <div class="mt-5 grid gap-5 md:grid-cols-2 xl:grid-cols-3">
                @foreach ($plans as $plan)
                    <x-plan-card :plan="$plan" />
                @endforeach
            </div>

            @if ($plans->hasPages())
                <div class="mt-6 rounded-2xl border border-slate-200 bg-white px-5 py-4 shadow-card">{{ $plans->links() }}</div>
            @endif
        @endif
    </section>
@endsection
