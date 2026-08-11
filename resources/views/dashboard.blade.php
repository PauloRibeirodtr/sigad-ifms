@extends('layouts.app')

@section('title', 'Planos de Trabalho')
@section('page_title', request()->routeIs('dashboard') ? 'Painel inicial' : 'Planos de Trabalho')
@section('page_subtitle', 'Visão geral dos seus Planos de Trabalho')

@section('content')
    <section aria-labelledby="planos-heading">
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
