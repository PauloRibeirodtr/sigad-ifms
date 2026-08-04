@extends('layouts.app')

@section('title', $plan->nome)
@section('page_title', 'Plano de Trabalho')
@section('page_subtitle', 'Detalhes e período de vigência')

@section('content')
    <div class="grid gap-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <a href="{{ route('plans.index') }}" class="inline-flex items-center gap-2 text-sm font-bold text-brand-700 transition hover:text-brand-900"><span aria-hidden="true">←</span> Voltar para planos</a>
            <a href="{{ route('plans.edit', $plan) }}" class="inline-flex min-h-11 items-center justify-center rounded-xl border border-slate-200 bg-white px-5 text-sm font-bold text-slate-700 transition hover:bg-slate-100">Editar Plano de Trabalho</a>
        </div>

        <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-card">
            <div class="bg-brand-800 px-6 py-7 text-white sm:px-8">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <p class="text-sm font-bold text-brand-100">Plano #{{ $plan->id }}</p>
                        <h2 class="mt-1 text-2xl font-extrabold tracking-tight sm:text-3xl">{{ $plan->nome }}</h2>
                    </div>
                    <x-plan-status-badge :status="$plan->status" class="bg-white/95" />
                </div>
            </div>

            <div class="grid gap-7 p-6 sm:p-8">
                <div>
                    <h3 class="text-xs font-extrabold uppercase tracking-[0.16em] text-slate-400">Descrição</h3>
                    <p class="mt-3 whitespace-pre-line text-sm leading-7 text-slate-700">{{ $plan->descricao ?: 'Nenhuma descrição informada.' }}</p>
                </div>

                <dl class="grid gap-4 border-y border-slate-100 py-6 sm:grid-cols-3">
                    <div class="rounded-2xl bg-slate-50 p-4"><dt class="text-xs font-bold text-slate-500">Data inicial</dt><dd class="mt-2 text-base font-extrabold text-slate-900">{{ $plan->data_inicial->format('d/m/Y') }}</dd></div>
                    <div class="rounded-2xl bg-slate-50 p-4"><dt class="text-xs font-bold text-slate-500">Data final</dt><dd class="mt-2 text-base font-extrabold text-slate-900">{{ $plan->data_final->format('d/m/Y') }}</dd></div>
                    <div class="rounded-2xl bg-slate-50 p-4"><dt class="text-xs font-bold text-slate-500">Última atualização</dt><dd class="mt-2 text-base font-extrabold text-slate-900">{{ $plan->updated_at->format('d/m/Y H:i') }}</dd></div>
                </dl>

                <div class="rounded-2xl border border-slate-200 bg-slate-50 px-5 py-6">
                    <h3 class="font-extrabold text-slate-900">Atividades do Plano de Trabalho</h3>
                    <dl class="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-4">
                        <div class="rounded-xl bg-white p-3 text-center"><dt class="text-xs text-slate-500">Total</dt><dd class="mt-1 text-xl font-extrabold">{{ $plan->atividades_count }}</dd></div>
                        <div class="rounded-xl bg-white p-3 text-center"><dt class="text-xs text-slate-500">Aguardando</dt><dd class="mt-1 text-xl font-extrabold">{{ $plan->atividades_aguardando_count }}</dd></div>
                        <div class="rounded-xl bg-white p-3 text-center"><dt class="text-xs text-slate-500">Em andamento</dt><dd class="mt-1 text-xl font-extrabold text-warning-700">{{ $plan->atividades_em_andamento_count }}</dd></div>
                        <div class="rounded-xl bg-white p-3 text-center"><dt class="text-xs text-slate-500">Concluídas</dt><dd class="mt-1 text-xl font-extrabold text-brand-700">{{ $plan->atividades_concluidas_count }}</dd></div>
                    </dl>
                    <div class="mt-4 grid gap-3 sm:grid-cols-3">
                        <a href="{{ route('plans.activities.index', ['plano' => $plan, 'indicador' => 'atrasadas']) }}" class="rounded-xl border border-red-100 bg-red-50 p-3 text-center"><span class="block text-xs font-bold text-red-700">Atrasadas</span><strong class="mt-1 block text-xl text-red-800">{{ $plan->atividades_atrasadas_count }}</strong></a>
                        <a href="{{ route('plans.activities.index', ['plano' => $plan, 'indicador' => 'urgentes']) }}" class="rounded-xl border border-brand-100 bg-brand-50 p-3 text-center"><span class="block text-xs font-bold text-brand-700">Urgentes</span><strong class="mt-1 block text-xl text-brand-800">{{ $plan->atividades_urgentes_count }}</strong></a>
                        <a href="{{ route('plans.activities.index', ['plano' => $plan, 'indicador' => 'sem_atualizacao']) }}" class="rounded-xl border border-slate-200 bg-white p-3 text-center"><span class="block text-xs font-bold text-slate-600">Sem atualização recente</span><strong class="mt-1 block text-xl text-slate-800">{{ $plan->atividades_sem_atualizacao_count }}</strong></a>
                    </div>
                    <div class="mt-5 flex flex-wrap gap-3">
                        <a href="{{ route('plans.activities.index', $plan) }}" class="inline-flex min-h-11 items-center justify-center rounded-xl bg-brand-700 px-5 text-sm font-bold text-white transition hover:bg-brand-800">Ver atividades</a>
                        <a href="{{ route('plans.activities.create', $plan) }}" class="inline-flex min-h-11 items-center justify-center rounded-xl border border-brand-200 bg-white px-5 text-sm font-bold text-brand-700 transition hover:bg-brand-50">Nova atividade</a>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection
