@extends('layouts.app')

@section('title', 'Relatórios')
@section('page_title', 'Relatórios por Plano de Trabalho')
@section('page_subtitle', 'Consulte planos encerrados e gere um relatório individual')

@section('content')
    <div class="grid gap-6">
        <section class="rounded-3xl border border-slate-200 bg-white p-5 shadow-card sm:p-6">
            <div class="max-w-3xl">
                <p class="text-xs font-extrabold uppercase tracking-[0.16em] text-brand-700">Período de encerramento</p>
                <h2 class="mt-2 text-2xl font-extrabold tracking-tight text-slate-950">Localize o Plano de Trabalho</h2>
                <p class="mt-2 text-sm leading-6 text-slate-500">A pesquisa considera a data final do plano. Somente planos já encerrados e pertencentes à sua conta serão exibidos.</p>
            </div>

            <form method="GET" action="{{ route('reports.index') }}" class="mt-6 grid gap-4 border-t border-slate-100 pt-6 sm:grid-cols-2 lg:grid-cols-[1fr_1fr_auto_auto] lg:items-end">
                <x-form-input name="data_inicial" label="Encerrados a partir de" type="date" :value="request('data_inicial')" />
                <x-form-input name="data_final" label="Encerrados até" type="date" :value="request('data_final')" />
                <a href="{{ route('reports.index') }}" class="inline-flex min-h-12 items-center justify-center rounded-xl border border-slate-200 px-5 text-sm font-bold text-slate-600 transition hover:bg-slate-50">Limpar</a>
                <button type="submit" class="inline-flex min-h-12 items-center justify-center gap-2 rounded-xl bg-brand-700 px-6 text-sm font-bold text-white transition hover:bg-brand-800">
                    <svg viewBox="0 0 24 24" class="size-5" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m16 16 4 4" stroke-linecap="round"/></svg>
                    Pesquisar
                </button>
            </form>
        </section>

        @if ($plans === null)
            <section class="rounded-3xl border border-dashed border-brand-200 bg-brand-50/60 px-6 py-14 text-center">
                <span class="mx-auto grid size-14 place-items-center rounded-2xl bg-white text-brand-700 shadow-sm">
                    <svg viewBox="0 0 24 24" class="size-7" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M4 21V10m5 11V4m6 17v-7m5 7V7M2 21h20" stroke-linecap="round"/></svg>
                </span>
                <h3 class="mt-4 text-lg font-extrabold text-slate-900">Informe um período para começar</h3>
                <p class="mt-2 text-sm text-slate-500">Os resultados aparecerão aqui, um plano por relatório.</p>
            </section>
        @elseif ($plans->isEmpty())
            <section class="rounded-3xl border border-slate-200 bg-white px-6 py-14 text-center shadow-card">
                <h3 class="text-lg font-extrabold text-slate-900">Nenhum plano encerrado no período</h3>
                <p class="mt-2 text-sm text-slate-500">Revise as datas informadas e faça uma nova pesquisa.</p>
            </section>
        @else
            <section>
                <div class="mb-4 flex items-end justify-between gap-4">
                    <div>
                        <p class="text-xs font-extrabold uppercase tracking-[0.16em] text-brand-700">Resultado da pesquisa</p>
                        <h2 class="mt-1 text-xl font-extrabold text-slate-950">{{ $plans->total() }} {{ $plans->total() === 1 ? 'plano encontrado' : 'planos encontrados' }}</h2>
                    </div>
                </div>

                <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                    @foreach ($plans as $plan)
                        <article class="flex flex-col rounded-3xl border border-slate-200 bg-white p-5 shadow-card">
                            <div class="flex items-start justify-between gap-3">
                                <span class="grid size-11 shrink-0 place-items-center rounded-2xl bg-brand-50 text-brand-700">
                                    <svg viewBox="0 0 24 24" class="size-6" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M4 5.5A2.5 2.5 0 0 1 6.5 3H10l2 2h5.5A2.5 2.5 0 0 1 20 7.5v10a2.5 2.5 0 0 1-2.5 2.5h-11A2.5 2.5 0 0 1 4 17.5v-12Z" stroke-linejoin="round"/></svg>
                                </span>
                                <x-plan-status-badge :status="$plan->status" />
                            </div>
                            <h3 class="mt-4 text-lg font-extrabold text-slate-950">{{ $plan->nome }}</h3>
                            <p class="mt-1 line-clamp-2 text-sm leading-6 text-slate-500">{{ $plan->descricao ?: 'Sem descrição informada.' }}</p>
                            <dl class="mt-5 grid grid-cols-2 gap-3 border-t border-slate-100 pt-4 text-sm">
                                <div><dt class="text-xs font-bold text-slate-400">Período</dt><dd class="mt-1 font-semibold text-slate-700">{{ $plan->data_inicial->format('d/m/Y') }} a {{ $plan->data_final->format('d/m/Y') }}</dd></div>
                                <div><dt class="text-xs font-bold text-slate-400">Atividades</dt><dd class="mt-1 font-extrabold text-slate-900">{{ $plan->atividades_count }}</dd></div>
                            </dl>
                            <a href="{{ route('reports.show', $plan) }}" class="mt-5 inline-flex min-h-11 items-center justify-center gap-2 rounded-xl bg-brand-700 px-4 text-sm font-bold text-white transition hover:bg-brand-800">Gerar relatório <span aria-hidden="true">→</span></a>
                        </article>
                    @endforeach
                </div>

                @if ($plans->hasPages())
                    <div class="mt-6 rounded-2xl border border-slate-200 bg-white px-5 py-4">{{ $plans->links() }}</div>
                @endif
            </section>
        @endif
    </div>
@endsection
