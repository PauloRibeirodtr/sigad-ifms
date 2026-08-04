@extends('layouts.app')

@section('title', 'Atividades')
@section('page_title', 'Atividades do Plano de Trabalho')
@section('page_subtitle', $plano->nome)

@section('content')
    <div class="grid gap-6">
        <section class="rounded-3xl border border-slate-200 bg-white p-5 shadow-card sm:p-6">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <div class="flex flex-wrap items-center gap-3"><a href="{{ route('plans.show', $plano) }}" class="text-sm font-bold text-brand-700">← Plano</a><x-plan-status-badge :status="$plano->status" /></div>
                    <h2 class="mt-3 text-2xl font-extrabold text-slate-950">{{ $plano->nome }}</h2>
                    <p class="mt-1 text-sm text-slate-500">{{ $activities->total() }} {{ $activities->total() === 1 ? 'atividade encontrada' : 'atividades encontradas' }}</p>
                </div>
                <a href="{{ route('plans.activities.create', $plano) }}" class="inline-flex min-h-12 items-center justify-center gap-2 rounded-xl bg-brand-700 px-5 text-sm font-bold text-white"><span class="text-xl">+</span> Nova atividade</a>
            </div>

            <div class="mt-6 grid gap-3 border-t border-slate-100 pt-5 sm:grid-cols-2 xl:grid-cols-4">
                @foreach (\App\Enums\AtividadeIndicador::cases() as $indicatorOption)
                    @php
                        $indicatorTone = match ($indicatorOption) {
                            \App\Enums\AtividadeIndicador::Atrasadas => 'border-red-200 bg-red-50 text-red-800',
                            \App\Enums\AtividadeIndicador::Aguardando => 'border-warning-100 bg-warning-50 text-warning-700',
                            \App\Enums\AtividadeIndicador::Urgentes => 'border-brand-200 bg-brand-50 text-brand-800',
                            \App\Enums\AtividadeIndicador::SemAtualizacao => 'border-slate-200 bg-slate-100 text-slate-700',
                        };
                    @endphp
                    <a href="{{ route('plans.activities.index', ['plano' => $plano, 'indicador' => $indicatorOption->value]) }}" @class(['flex items-center justify-between gap-3 rounded-2xl border px-4 py-3 transition hover:-translate-y-0.5', $indicatorTone, 'ring-2 ring-brand-300' => $indicator === $indicatorOption])>
                        <span class="text-xs font-extrabold">{{ $indicatorOption->label() }}</span>
                        <strong class="text-xl">{{ $indicatorCounts[$indicatorOption->value] }}</strong>
                    </a>
                @endforeach
            </div>

            <details class="mt-5 border-t border-slate-100 pt-5" @if(request()->hasAny(['busca','indicador','status','categoria_id','prioridade','titulo','solicitante','periodo_inicial','periodo_final','prazo'])) open @endif>
                <summary class="cursor-pointer text-sm font-extrabold text-brand-700">Busca e filtros</summary>
                <form method="GET" action="{{ route('plans.activities.index', $plano) }}" class="mt-5 grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                    <x-form-input name="busca" label="Busca geral" :value="request('busca')" placeholder="Título, descrição, solicitante ou categoria" />
                    <x-form-input name="titulo" label="Título" :value="request('titulo')" />
                    <x-form-input name="solicitante" label="Solicitante" :value="request('solicitante')" />
                    <div class="grid gap-2"><label for="indicador" class="text-sm font-semibold text-slate-700">Indicador</label><select id="indicador" name="indicador" class="min-h-12 rounded-xl border border-slate-200 bg-white px-4 text-sm"><option value="">Todos</option>@foreach(\App\Enums\AtividadeIndicador::cases() as $indicatorOption)<option value="{{ $indicatorOption->value }}" @selected($indicator === $indicatorOption)>{{ $indicatorOption->label() }}</option>@endforeach</select></div>
                    <div class="grid gap-2"><label for="status" class="text-sm font-semibold text-slate-700">Status</label><select id="status" name="status" class="min-h-12 rounded-xl border border-slate-200 bg-white px-4 text-sm"><option value="">Todos</option>@foreach(\App\Enums\AtividadeStatus::cases() as $status)<option value="{{ $status->value }}" @selected(request('status') === $status->value)>{{ $status->label() }}</option>@endforeach</select></div>
                    <div class="grid gap-2"><label for="prioridade" class="text-sm font-semibold text-slate-700">Prioridade</label><select id="prioridade" name="prioridade" class="min-h-12 rounded-xl border border-slate-200 bg-white px-4 text-sm"><option value="">Todas</option>@foreach(\App\Enums\AtividadePrioridade::cases() as $priority)<option value="{{ $priority->value }}" @selected(request('prioridade') === $priority->value)>{{ $priority->label() }}</option>@endforeach</select></div>
                    <div class="grid gap-2"><label for="categoria_id" class="text-sm font-semibold text-slate-700">Categoria</label><select id="categoria_id" name="categoria_id" class="min-h-12 rounded-xl border border-slate-200 bg-white px-4 text-sm"><option value="">Todas</option>@foreach($categories as $category)<option value="{{ $category->id }}" @selected((string) request('categoria_id') === (string) $category->id)>{{ $category->nome }}{{ ! $category->ativa ? ' (inativa)' : '' }}</option>@endforeach</select></div>
                    <div class="grid gap-2"><label for="prazo_filtro" class="text-sm font-semibold text-slate-700">Prazo</label><select id="prazo_filtro" name="prazo" class="min-h-12 rounded-xl border border-slate-200 bg-white px-4 text-sm"><option value="">Todos</option><option value="atrasado" @selected(request('prazo') === 'atrasado')>Atrasadas</option><option value="com_prazo" @selected(request('prazo') === 'com_prazo')>Com prazo</option><option value="sem_prazo" @selected(request('prazo') === 'sem_prazo')>Sem prazo</option></select></div>
                    <x-form-input name="periodo_inicial" label="Período inicial" type="date" :value="request('periodo_inicial')" />
                    <x-form-input name="periodo_final" label="Período final" type="date" :value="request('periodo_final')" />
                    <div class="flex items-end gap-2 sm:col-span-2 xl:col-span-4 xl:justify-end"><a href="{{ route('plans.activities.index', $plano) }}" class="inline-flex min-h-11 items-center rounded-xl border border-slate-200 px-4 text-sm font-bold text-slate-600">Limpar</a><button class="inline-flex min-h-11 items-center rounded-xl bg-brand-700 px-5 text-sm font-bold text-white">Aplicar filtros</button></div>
                </form>
            </details>
        </section>

        <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-card">
            @if ($activities->isEmpty())
                <div class="px-6 py-16 text-center"><h3 class="text-lg font-extrabold text-slate-900">Nenhuma atividade encontrada</h3><p class="mt-2 text-sm text-slate-500">Cadastre uma atividade ou revise os filtros.</p></div>
            @else
                <div class="hidden overflow-x-auto xl:block">
                    <table class="w-full min-w-[1380px] text-left text-sm">
                        <thead class="border-b border-slate-200 bg-slate-50 text-xs font-extrabold uppercase tracking-wide text-slate-500"><tr><th class="px-5 py-4">Data e título</th><th class="px-4 py-4">Categoria</th><th class="px-4 py-4">Solicitante</th><th class="px-4 py-4">Prioridade</th><th class="px-4 py-4">Status</th><th class="px-4 py-4">Indicadores</th><th class="px-4 py-4">Prazo</th><th class="px-4 py-4">Última movimentação</th><th class="px-5 py-4 text-right">Ações</th></tr></thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($activities as $activity)
                                @php
                                    $overdue = $activity->isOverdue();
                                    $withoutRecentUpdate = $activity->isWithoutRecentUpdate();
                                @endphp
                                <tr class="align-top hover:bg-slate-50/70">
                                    <td class="px-5 py-5"><p class="text-xs text-slate-500">{{ $activity->data_atividade->format('d/m/Y') }}</p><p class="mt-1 font-bold text-slate-950">{{ $activity->titulo }}</p></td>
                                    <td class="px-4 py-5 text-xs text-slate-600">{{ $activity->categoria->nome }}</td>
                                    <td class="px-4 py-5 text-xs text-slate-600">{{ $activity->solicitante ?: '—' }}</td>
                                    <td class="px-4 py-5"><x-activity-priority-badge :priority="$activity->prioridade" /></td>
                                    <td class="px-4 py-5"><x-activity-status-badge :status="$activity->status" />@if($activity->aguardando_descricao)<span class="mt-2 block max-w-40 text-xs text-slate-500">{{ $activity->aguardando_descricao }}</span>@endif</td>
                                    <td class="px-4 py-5"><div class="flex max-w-44 flex-wrap gap-1.5">@if($overdue)<span class="rounded-full bg-red-50 px-2.5 py-1 text-[0.65rem] font-extrabold text-red-700">Atrasada</span>@endif @if($withoutRecentUpdate)<span class="rounded-full bg-slate-100 px-2.5 py-1 text-[0.65rem] font-extrabold text-slate-600">Sem atualização</span>@endif @if($activity->prioridade === \App\Enums\AtividadePrioridade::Urgente && ! in_array($activity->status, [\App\Enums\AtividadeStatus::Concluida, \App\Enums\AtividadeStatus::Cancelada], true))<span class="rounded-full bg-brand-50 px-2.5 py-1 text-[0.65rem] font-extrabold text-brand-700">Urgente</span>@endif</div></td>
                                    <td class="px-4 py-5 text-xs font-bold {{ $overdue ? 'text-red-700' : 'text-slate-600' }}">{{ $activity->prazo?->format('d/m/Y') ?? '—' }}</td>
                                    <td class="px-4 py-5 text-xs font-semibold {{ $withoutRecentUpdate ? 'text-slate-800' : 'text-slate-500' }}">{{ $activity->ultima_movimentacao_em?->format('d/m/Y') ?? '—' }}</td>
                                    <td class="px-5 py-5"><div class="flex justify-end gap-2"><a href="{{ route('plans.activities.show', [$plano, $activity]) }}" class="inline-flex min-h-9 items-center rounded-lg bg-brand-700 px-3 text-xs font-bold text-white">Ver</a><a href="{{ route('plans.activities.movements.create', [$plano, $activity]) }}" class="inline-flex min-h-9 items-center rounded-lg border border-brand-200 bg-white px-3 text-xs font-bold text-brand-700">Movimentar</a></div></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="grid gap-4 p-4 md:grid-cols-2 xl:hidden">
                    @foreach ($activities as $activity)
                        @php
                            $overdue = $activity->isOverdue();
                            $withoutRecentUpdate = $activity->isWithoutRecentUpdate();
                        @endphp
                        <article class="grid gap-4 rounded-2xl border p-5 {{ $overdue ? 'border-red-200 bg-red-50/30' : 'border-slate-200' }}">
                            <div class="flex items-start justify-between gap-3"><div><p class="text-xs text-slate-500">{{ $activity->data_atividade->format('d/m/Y') }} · {{ $activity->categoria->nome }}</p><h3 class="mt-1 font-extrabold text-slate-950">{{ $activity->titulo }}</h3></div><x-activity-status-badge :status="$activity->status" /></div>
                            <div class="flex flex-wrap gap-2"><x-activity-priority-badge :priority="$activity->prioridade" />@if($overdue)<span class="rounded-full bg-red-100 px-2.5 py-1 text-xs font-extrabold text-red-700">Atrasada</span>@endif @if($withoutRecentUpdate)<span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-extrabold text-slate-600">Sem atualização</span>@endif</div>
                            <dl class="grid grid-cols-2 gap-3 text-xs"><div><dt class="font-bold text-slate-500">Prazo</dt><dd class="mt-1 {{ $overdue ? 'font-extrabold text-red-700' : 'text-slate-700' }}">{{ $activity->prazo?->format('d/m/Y') ?? '—' }}</dd></div><div><dt class="font-bold text-slate-500">Última movimentação</dt><dd class="mt-1 text-slate-700">{{ $activity->ultima_movimentacao_em?->format('d/m/Y') ?? '—' }}</dd></div></dl>
                            <div class="flex gap-2 border-t border-slate-100 pt-4"><a href="{{ route('plans.activities.show', [$plano, $activity]) }}" class="inline-flex min-h-10 flex-1 items-center justify-center rounded-lg bg-brand-700 px-3 text-xs font-bold text-white">Visualizar</a><a href="{{ route('plans.activities.movements.create', [$plano, $activity]) }}" class="inline-flex min-h-10 flex-1 items-center justify-center rounded-lg border border-brand-200 bg-white px-3 text-xs font-bold text-brand-700">Movimentar</a></div>
                        </article>
                    @endforeach
                </div>
                @if($activities->hasPages())<div class="border-t border-slate-200 px-5 py-5">{{ $activities->links() }}</div>@endif
            @endif
        </section>
    </div>
@endsection
