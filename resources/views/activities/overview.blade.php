@extends('layouts.app')

@section('title', 'Atividades')
@section('page_title', 'Atividades')
@section('page_subtitle', 'Indicadores por PAT')

@section('content')
    <section class="grid gap-6">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="text-sm font-bold text-brand-700">Acompanhamento operacional</p>
                <h2 class="mt-1 text-2xl font-extrabold tracking-tight text-slate-950">Atividades por PAT</h2>
                <p class="mt-2 text-sm text-slate-500">Os indicadores consideram exclusivamente seus registros e usam a data das movimentações.</p>
            </div>
            <form method="GET" action="{{ route('activities.overview') }}" class="flex w-full max-w-xl flex-col gap-2 sm:flex-row">
                @if ($indicator)<input type="hidden" name="indicador" value="{{ $indicator->value }}">@endif
                <label for="busca_plano" class="sr-only">Buscar PAT</label>
                <input id="busca_plano" name="busca" value="{{ request('busca') }}" placeholder="Buscar PAT" class="min-h-12 min-w-0 flex-1 rounded-xl border border-slate-200 bg-white px-4 text-sm shadow-sm focus:border-brand-500 focus:ring-4 focus:ring-brand-100">
                <button class="min-h-12 rounded-xl bg-brand-700 px-5 text-sm font-bold text-white">Buscar</button>
            </form>
        </div>

        <nav class="flex gap-2 overflow-x-auto pb-1" aria-label="Indicadores de atividades">
            <a href="{{ route('activities.overview', request()->only('busca')) }}" class="whitespace-nowrap rounded-full px-4 py-2 text-sm font-bold {{ $indicator === null ? 'bg-brand-700 text-white' : 'border border-slate-200 bg-white text-slate-600' }}">Todos os PATs</a>
            @foreach (\App\Enums\AtividadeIndicador::cases() as $indicatorOption)
                <a href="{{ route('activities.overview', array_filter(['indicador' => $indicatorOption->value, 'busca' => request('busca')])) }}" class="whitespace-nowrap rounded-full px-4 py-2 text-sm font-bold {{ $indicator === $indicatorOption ? 'bg-brand-700 text-white' : 'border border-slate-200 bg-white text-slate-600' }}">{{ $indicatorOption->label() }}</a>
            @endforeach
        </nav>

        @if ($indicator)
            <x-alert type="info">Exibindo somente PATs com atividades classificadas como <strong>{{ mb_strtolower($indicator->label()) }}</strong>.</x-alert>
        @endif

        @if ($plans->isEmpty())
            <div class="rounded-3xl border border-dashed border-brand-300 bg-white px-6 py-16 text-center shadow-card">
                <h3 class="text-lg font-extrabold text-slate-900">Nenhum PAT encontrado</h3>
                <p class="mt-2 text-sm text-slate-500">Não há PATs correspondentes à busca ou ao indicador selecionado.</p>
                <a href="{{ route('activities.overview') }}" class="mt-5 inline-flex min-h-11 items-center rounded-xl border border-brand-200 bg-brand-50 px-5 text-sm font-bold text-brand-700">Limpar pesquisa</a>
            </div>
        @else
            <p class="text-sm font-semibold text-slate-500">{{ $plans->total() }} {{ $plans->total() === 1 ? 'PAT encontrado' : 'PATs encontrados' }}</p>
            <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-3">
                @foreach ($plans as $plan)
                    <article class="flex flex-col rounded-3xl border border-slate-200 bg-white p-6 shadow-card">
                        <div class="flex items-start justify-between gap-4">
                            <span class="grid size-11 place-items-center rounded-2xl bg-brand-50 text-brand-700"><svg viewBox="0 0 24 24" class="size-6" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M4 5.5A2.5 2.5 0 0 1 6.5 3H10l2 2h5.5A2.5 2.5 0 0 1 20 7.5v10a2.5 2.5 0 0 1-2.5 2.5h-11A2.5 2.5 0 0 1 4 17.5v-12Z"/></svg></span>
                            <x-plan-status-badge :status="$plan->status" />
                        </div>
                        <h3 class="mt-5 text-lg font-extrabold text-slate-950">{{ $plan->nome }}</h3>
                            <p class="mt-1 text-xs font-bold text-brand-700">PIT {{ $plan->pit->nome }}</p>
                            <p class="mt-2 text-sm text-slate-500">{{ $plan->data_inicial->format('d/m/Y') }} a {{ $plan->data_final->format('d/m/Y') }}</p>

                        <dl class="mt-5 grid grid-cols-2 gap-2 text-center">
                            <div class="rounded-xl bg-slate-50 p-3"><dt class="text-[0.65rem] font-bold text-slate-500">Total</dt><dd class="mt-1 text-xl font-extrabold text-slate-900">{{ $plan->atividades_count }}</dd></div>
                            <div class="rounded-xl bg-red-50 p-3"><dt class="text-[0.65rem] font-bold text-red-700">Atrasadas</dt><dd class="mt-1 text-xl font-extrabold text-red-800">{{ $plan->atividades_atrasadas_count }}</dd></div>
                            <div class="rounded-xl bg-warning-50 p-3"><dt class="text-[0.65rem] font-bold text-warning-700">Aguardando</dt><dd class="mt-1 text-xl font-extrabold text-warning-700">{{ $plan->atividades_aguardando_count }}</dd></div>
                            <div class="rounded-xl bg-brand-50 p-3"><dt class="text-[0.65rem] font-bold text-brand-700">Urgentes</dt><dd class="mt-1 text-xl font-extrabold text-brand-800">{{ $plan->atividades_urgentes_count }}</dd></div>
                            <div class="col-span-2 rounded-xl bg-slate-100 p-3"><dt class="text-[0.65rem] font-bold text-slate-600">Sem atualização recente</dt><dd class="mt-1 text-xl font-extrabold text-slate-800">{{ $plan->atividades_sem_atualizacao_count }}</dd></div>
                        </dl>

                        <a href="{{ route('plans.activities.index', array_filter(['plano' => $plan, 'indicador' => $indicator?->value])) }}" class="mt-6 inline-flex min-h-11 items-center justify-center rounded-xl bg-brand-700 px-5 text-sm font-bold text-white transition hover:bg-brand-800">{{ $indicator ? 'Ver atividades filtradas' : 'Abrir atividades' }}</a>
                    </article>
                @endforeach
            </div>
            @if ($plans->hasPages())<div class="rounded-2xl border border-slate-200 bg-white px-5 py-4">{{ $plans->links() }}</div>@endif
        @endif
    </section>
@endsection
