@extends('layouts.app')

@section('title', $atividade->titulo)
@section('page_title', 'Detalhes da atividade')
@section('page_subtitle', $plano->nome)

@section('content')
    <div class="grid gap-6">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <a href="{{ route('plans.activities.index', $plano) }}" class="text-sm font-bold text-brand-700">← Voltar para atividades</a>
            <div class="flex flex-col gap-2 sm:flex-row">
                <a href="{{ route('plans.activities.edit', [$plano, $atividade]) }}" class="inline-flex min-h-11 items-center justify-center rounded-xl border border-slate-200 bg-white px-5 text-sm font-bold text-slate-700">Editar dados gerais</a>
                <a href="{{ route('plans.activities.movements.create', [$plano, $atividade]) }}" class="inline-flex min-h-11 items-center justify-center rounded-xl bg-brand-700 px-5 text-sm font-bold text-white shadow-sm transition hover:bg-brand-800">+ Nova movimentação</a>
            </div>
        </div>

        <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-card">
            <div class="bg-brand-800 px-6 py-7 text-white sm:px-8">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <p class="text-sm font-bold text-brand-100">Atividade #{{ $atividade->id }}</p>
                        <h2 class="mt-1 text-2xl font-extrabold sm:text-3xl">{{ $atividade->titulo }}</h2>
                        <p class="mt-2 text-sm text-brand-100">{{ $plano->nome }}</p>
                    </div>
                    <x-activity-status-badge :status="$atividade->status" class="bg-white/95" />
                </div>
            </div>

            <div class="grid gap-7 p-6 sm:p-8">
                <dl class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                    <div class="rounded-2xl bg-slate-50 p-4"><dt class="text-xs font-bold text-slate-500">Data</dt><dd class="mt-2 font-extrabold text-slate-900">{{ $atividade->data_atividade->format('d/m/Y') }}</dd></div>
                    <div class="rounded-2xl bg-slate-50 p-4"><dt class="text-xs font-bold text-slate-500">Categoria</dt><dd class="mt-2 font-extrabold text-slate-900">{{ $atividade->categoria->nome }}</dd></div>
                    <div class="rounded-2xl bg-slate-50 p-4"><dt class="text-xs font-bold text-slate-500">Prioridade</dt><dd class="mt-2"><x-activity-priority-badge :priority="$atividade->prioridade" /></dd></div>
                    <div class="rounded-2xl bg-slate-50 p-4"><dt class="text-xs font-bold text-slate-500">Prazo</dt><dd class="mt-2 font-extrabold text-slate-900">{{ $atividade->prazo?->format('d/m/Y') ?? 'Não definido' }}</dd></div>
                    <div class="rounded-2xl bg-slate-50 p-4"><dt class="text-xs font-bold text-slate-500">Solicitante</dt><dd class="mt-2 font-extrabold text-slate-900">{{ $atividade->solicitante ?: 'Não informado' }}</dd></div>
                    <div class="rounded-2xl bg-slate-50 p-4 sm:col-span-2 xl:col-span-3"><dt class="text-xs font-bold text-slate-500">Próxima ação</dt><dd class="mt-2 font-semibold text-slate-900">{{ $atividade->proxima_acao ?: 'Não definida' }}</dd></div>
                </dl>

                @if ($atividade->status === \App\Enums\AtividadeStatus::Aguardando)
                    <x-alert type="info">Aguardando por: <strong>{{ $atividade->aguardando_por?->label() }}</strong>{{ $atividade->aguardando_descricao ? ' — '.$atividade->aguardando_descricao : '' }}</x-alert>
                @endif

                <div>
                    <h3 class="text-xs font-extrabold uppercase tracking-[0.16em] text-slate-400">Descrição geral</h3>
                    <p class="mt-3 whitespace-pre-line text-sm leading-7 text-slate-700">{{ $atividade->descricao ?: 'Nenhuma descrição geral informada.' }}</p>
                </div>
            </div>
        </section>

        <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-card sm:p-8">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <p class="text-sm font-bold text-brand-700">Trilha cronológica</p>
                    <h2 class="mt-1 text-2xl font-extrabold text-slate-950">Histórico de movimentações</h2>
                    <p class="mt-2 text-sm text-slate-500">Ordem: data da ação, registro e identificador. A última define o estado atual.</p>
                </div>
                <span class="text-sm font-bold text-slate-500">{{ $atividade->movimentacoes->count() }} {{ $atividade->movimentacoes->count() === 1 ? 'registro' : 'registros' }}</span>
            </div>

            <ol class="mt-7 grid gap-5">
                @foreach ($atividade->movimentacoes as $movement)
                    <li class="relative rounded-2xl border p-5 sm:p-6 {{ $movement->id === $currentMovementId ? 'border-brand-300 bg-brand-50/40' : 'border-slate-200' }}">
                        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-2">
                                    <p class="text-xs font-bold text-slate-500">{{ $movement->data_movimentacao->format('d/m/Y') }}</p>
                                    @if ($movement->id === $currentMovementId)
                                        <span class="rounded-full bg-brand-100 px-2.5 py-1 text-[10px] font-extrabold uppercase tracking-wide text-brand-800">Estado atual</span>
                                    @endif
                                </div>
                                <p class="mt-3 whitespace-pre-line text-sm leading-6 text-slate-700">{{ $movement->descricao }}</p>
                            </div>
                            <x-activity-status-badge :status="$movement->status" />
                        </div>

                        <dl class="mt-4 flex flex-wrap gap-x-6 gap-y-2 border-t border-slate-200/80 pt-4 text-xs text-slate-500">
                            @if ($movement->aguardando_por)
                                <div><dt class="inline font-bold">Aguardando:</dt> <dd class="inline">{{ $movement->aguardando_por->label() }}{{ $movement->aguardando_descricao ? ' — '.$movement->aguardando_descricao : '' }}</dd></div>
                            @endif
                            <div><dt class="inline font-bold">Tempo:</dt> <dd class="inline">{{ $movement->minutos_trabalhados ? $movement->minutos_trabalhados.' minutos' : 'não informado' }}</dd></div>
                            <div><dt class="inline font-bold">Registrada em:</dt> <dd class="inline">{{ $movement->created_at->format('d/m/Y H:i:s') }}</dd></div>
                        </dl>

                        <div class="mt-4 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                            <div class="min-w-0">
                                @if ($movement->anexo_nome_original)
                                    <a href="{{ route('plans.activities.movements.download', [$plano, $atividade, $movement]) }}" class="inline-flex max-w-full items-center gap-2 text-sm font-bold text-brand-700 hover:text-brand-900">
                                        <span aria-hidden="true">↓</span><span class="truncate">{{ $movement->anexo_nome_original }}</span>
                                    </a>
                                @else
                                    <span class="text-xs text-slate-400">Sem anexo</span>
                                @endif
                            </div>
                            <a href="{{ route('plans.activities.movements.edit', [$plano, $atividade, $movement]) }}" class="inline-flex min-h-10 items-center justify-center rounded-xl border border-slate-200 bg-white px-4 text-xs font-bold text-slate-700 hover:border-brand-200 hover:text-brand-700">Editar movimentação</a>
                        </div>
                    </li>
                @endforeach
            </ol>
        </section>
    </div>
@endsection
