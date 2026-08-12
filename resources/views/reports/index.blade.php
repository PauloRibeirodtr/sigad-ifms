@extends('layouts.app')

@section('title', 'Relatórios')
@section('page_title', 'Relatórios por PAT')
@section('page_subtitle', 'Gere relatórios parciais ou finais a qualquer momento')

@section('content')
    <div class="grid gap-6">
        <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-card">
            <p class="text-xs font-extrabold uppercase tracking-[0.16em] text-brand-700">PITs e PATs</p>
            <h2 class="mt-2 text-2xl font-extrabold tracking-tight text-slate-950">Selecione um PAT</h2>
            <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-500">Todos os PITs cadastrados são exibidos abaixo. O relatório pode ser emitido durante ou após a vigência do PIT.</p>
        </section>

        @forelse ($pits as $pit)
            <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-card">
                <header class="flex flex-col gap-4 border-b border-slate-100 bg-slate-50 px-5 py-5 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                    <div><p class="text-xs font-extrabold uppercase tracking-[0.16em] text-brand-700">PIT</p><h2 class="mt-1 text-xl font-extrabold text-slate-950">{{ $pit->nome }}</h2><p class="mt-1 text-sm text-slate-500">{{ $pit->data_inicial->format('d/m/Y') }} a {{ $pit->data_final->format('d/m/Y') }}</p></div>
                    <x-plan-status-badge :status="$pit->status" />
                </header>

                @if ($pit->planosTrabalho->isEmpty())
                    <p class="px-6 py-8 text-sm text-slate-500">Este PIT ainda não possui PATs cadastrados.</p>
                @else
                    <div class="grid gap-4 p-5 sm:p-6 md:grid-cols-2 xl:grid-cols-3">
                        @foreach ($pit->planosTrabalho as $plan)
                            <article class="flex flex-col rounded-2xl border border-slate-200 p-5">
                                <p class="text-xs font-extrabold uppercase tracking-[0.14em] text-brand-700">PAT</p>
                                <h3 class="mt-2 text-lg font-extrabold text-slate-950">{{ $plan->nome }}</h3>
                                <p class="mt-2 line-clamp-2 text-sm leading-6 text-slate-500">{{ $plan->descricao ?: 'Sem descrição informada.' }}</p>
                                <p class="mt-4 text-xs font-semibold text-slate-500">{{ $plan->atividades_count }} {{ $plan->atividades_count === 1 ? 'atividade' : 'atividades' }}</p>
                                <a href="{{ route('reports.show', $plan) }}" class="mt-5 inline-flex min-h-11 items-center justify-center rounded-xl bg-brand-700 px-4 text-sm font-bold text-white">Gerar relatório <span class="ml-2" aria-hidden="true">→</span></a>
                            </article>
                        @endforeach
                    </div>
                @endif
            </section>
        @empty
            <section class="rounded-3xl border border-dashed border-brand-300 bg-white px-6 py-14 text-center shadow-card"><h3 class="text-lg font-extrabold text-slate-900">Nenhum PIT cadastrado</h3><p class="mt-2 text-sm text-slate-500">Cadastre um PIT e seus PATs para gerar relatórios.</p></section>
        @endforelse
    </div>
@endsection
