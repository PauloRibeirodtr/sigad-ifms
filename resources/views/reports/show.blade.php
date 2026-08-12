@extends('layouts.report')

@section('title', 'Relatório do PAT — '.$plan->nome)

@section('content')
    <article class="report-document overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-card print:rounded-none print:border-0 print:shadow-none">
        <header class="border-b border-slate-200 bg-linear-to-br from-brand-950 to-brand-700 px-6 py-8 text-white sm:px-10 print:border-b-2 print:border-brand-800 print:bg-white print:px-0 print:py-5 print:text-slate-950">
            <div class="flex flex-col gap-6 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <p class="text-xs font-extrabold uppercase tracking-[0.18em] text-brand-200 print:text-brand-700">SIGAD · Relatório de PAT</p>
                    <h1 class="mt-3 text-3xl font-extrabold tracking-tight">{{ $plan->nome }}</h1>
                    <p class="mt-2 max-w-3xl text-sm leading-6 text-white/75 print:text-slate-600">{{ $plan->descricao ?: 'Sem descrição informada.' }}</p>
                </div>
                <div class="shrink-0 rounded-2xl bg-white/10 px-4 py-3 text-sm ring-1 ring-white/15 print:bg-slate-50 print:text-slate-700 print:ring-slate-200">
                    <p class="text-xs font-bold uppercase tracking-wide text-white/60 print:text-slate-400">PIT {{ $plan->pit->nome }}</p>
                    <p class="mt-1 font-extrabold">{{ $plan->data_inicial->format('d/m/Y') }} a {{ $plan->data_final->format('d/m/Y') }}</p>
                </div>
            </div>
            <p class="mt-6 text-xs text-white/60 print:text-slate-500">Emitido em {{ now()->format('d/m/Y \à\s H:i') }} para {{ auth()->user()->name }}</p>
        </header>

        <div class="grid gap-8 px-6 py-8 sm:px-10 print:px-0 print:py-6">
            <section aria-labelledby="resumo-relatorio">
                <h2 id="resumo-relatorio" class="text-lg font-extrabold text-slate-950">Resumo quantitativo</h2>
                <dl class="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4"><dt class="text-xs font-bold text-slate-500">Atividades</dt><dd class="mt-1 text-2xl font-extrabold text-slate-950">{{ $resumo['atividades'] }}</dd></div>
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4"><dt class="text-xs font-bold text-slate-500">Movimentações</dt><dd class="mt-1 text-2xl font-extrabold text-slate-950">{{ $resumo['movimentacoes'] }}</dd></div>
                    <div class="rounded-2xl border border-brand-200 bg-brand-50 p-4"><dt class="text-xs font-bold text-brand-700">Tempo registrado</dt><dd class="mt-1 text-2xl font-extrabold text-brand-800">{{ $resumo['minutos'] }} min</dd></div>
                    <div class="rounded-2xl border border-warning-100 bg-warning-50 p-4"><dt class="text-xs font-bold text-warning-700">Registros sem tempo</dt><dd class="mt-1 text-2xl font-extrabold text-warning-700">{{ $resumo['sem_tempo'] }}</dd></div>
                    <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-4"><dt class="text-xs font-bold text-emerald-700">Atividades concluídas</dt><dd class="mt-1 text-2xl font-extrabold text-emerald-800">{{ $resumo['concluidas'] }}</dd></div>
                    <div class="rounded-2xl border border-red-200 bg-red-50 p-4"><dt class="text-xs font-bold text-red-700">Atividades canceladas</dt><dd class="mt-1 text-2xl font-extrabold text-red-800">{{ $resumo['canceladas'] }}</dd></div>
                </dl>
            </section>

            <section aria-labelledby="categorias-relatorio">
                <h2 id="categorias-relatorio" class="text-lg font-extrabold text-slate-950">Resumo por categoria</h2>
                @if ($categorias->isEmpty())
                    <p class="mt-3 rounded-2xl bg-slate-50 px-4 py-5 text-sm text-slate-500">O plano não possui atividades.</p>
                @else
                    <div class="mt-4 overflow-hidden rounded-2xl border border-slate-200">
                        <table class="w-full text-left text-sm">
                            <thead class="bg-slate-50 text-xs font-extrabold uppercase tracking-wide text-slate-500"><tr><th class="px-4 py-3">Categoria</th><th class="px-4 py-3 text-right">Atividades</th><th class="px-4 py-3 text-right">Movimentações</th><th class="px-4 py-3 text-right">Tempo</th></tr></thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach ($categorias as $categoria)
                                    <tr><td class="px-4 py-3 font-bold text-slate-800">{{ $categoria['nome'] }}</td><td class="px-4 py-3 text-right text-slate-600">{{ $categoria['atividades'] }}</td><td class="px-4 py-3 text-right text-slate-600">{{ $categoria['movimentacoes'] }}</td><td class="px-4 py-3 text-right text-slate-600">{{ $categoria['minutos'] }} min</td></tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </section>

            <section aria-labelledby="trilha-relatorio">
                <div>
                    <h2 id="trilha-relatorio" class="text-lg font-extrabold text-slate-950">Atividades e trilha de movimentações</h2>
                    <p class="mt-1 text-sm text-slate-500">Atividades pela data de realização; movimentações da mais antiga para a mais nova.</p>
                </div>

                @if ($atividades->isEmpty())
                    <p class="mt-4 rounded-2xl border border-dashed border-slate-300 px-5 py-8 text-center text-sm text-slate-500">Nenhuma atividade registrada neste plano.</p>
                @else
                    <div class="mt-5 grid gap-5">
                        @foreach ($atividades as $activity)
                            <article class="report-break-avoid rounded-2xl border border-slate-200">
                                <header class="border-b border-slate-100 bg-slate-50 px-5 py-4">
                                    <div class="flex flex-wrap items-start justify-between gap-3">
                                        <div>
                                            <p class="text-xs font-extrabold uppercase tracking-wide text-brand-700">{{ $activity->data_atividade->format('d/m/Y') }} · {{ $activity->categoria->nome }}</p>
                                            <h3 class="mt-1 text-base font-extrabold text-slate-950">{{ $activity->titulo }}</h3>
                                            <p class="mt-1 text-xs text-slate-500">Solicitante: {{ $activity->solicitante ?: 'Não informado' }}</p>
                                        </div>
                                        <x-activity-status-badge :status="$activity->status" />
                                    </div>
                                    @if ($activity->descricao)
                                        <p class="mt-3 text-sm leading-6 text-slate-600">{{ $activity->descricao }}</p>
                                    @endif
                                </header>

                                <div class="px-5 py-4">
                                    <h4 class="text-xs font-extrabold uppercase tracking-wide text-slate-500">Movimentações</h4>
                                    @if ($activity->movimentacoes->isEmpty())
                                        <p class="mt-3 text-sm text-slate-500">Nenhuma movimentação registrada.</p>
                                    @else
                                        <ol class="mt-3 grid gap-3 border-l-2 border-brand-100 pl-5">
                                            @foreach ($activity->movimentacoes as $movement)
                                                <li class="relative">
                                                <span class="absolute -left-[1.68rem] top-1.5 size-3 rounded-full border-2 border-brand-700 bg-white" aria-hidden="true"></span>
                                                <div class="flex flex-wrap items-center gap-2">
                                                    <time class="text-xs font-extrabold text-slate-700">{{ $movement->data_movimentacao->format('d/m/Y') }}</time>
                                                    <x-activity-status-badge :status="$movement->status" />
                                                    <span class="text-xs font-bold text-slate-500">{{ $movement->minutos_trabalhados === null ? 'Tempo não informado' : $movement->minutos_trabalhados.' min' }}</span>
                                                </div>
                                                <p class="mt-1 text-sm leading-6 text-slate-600">{{ $movement->descricao }}</p>
                                                @if ($movement->anexo_nome_original)
                                                    <a href="{{ route('plans.activities.movements.download', [$plan, $activity, $movement]) }}" class="mt-2 inline-flex items-center gap-1 text-xs font-bold text-brand-700 print:text-slate-600">Anexo: {{ $movement->anexo_nome_original }}</a>
                                                @endif
                                                </li>
                                            @endforeach
                                        </ol>
                                    @endif
                                </div>
                            </article>
                        @endforeach
                    </div>
                @endif
            </section>

            <section aria-labelledby="pendencias-relatorio" class="report-break-avoid">
                <h2 id="pendencias-relatorio" class="text-lg font-extrabold text-slate-950">Observações pendentes</h2>
                @if ($pendencias->isEmpty())
                    <p class="mt-3 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-4 text-sm font-semibold text-emerald-800">Não há atividades abertas ou aguardando no encerramento deste relatório.</p>
                @else
                    <ul class="mt-3 grid gap-2 rounded-2xl border border-warning-100 bg-warning-50 px-5 py-4 text-sm text-warning-700">
                        @foreach ($pendencias as $activity)
                            <li><strong>{{ $activity->titulo }}:</strong> {{ $activity->proxima_acao ?: 'sem próxima ação informada' }}.</li>
                        @endforeach
                    </ul>
                @endif
            </section>
        </div>
    </article>
@endsection
