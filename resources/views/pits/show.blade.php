@extends('layouts.app')

@section('title', 'PIT '.$pit->nome)
@section('page_title', 'PIT '.$pit->nome)
@section('page_subtitle', $pit->data_inicial->format('d/m/Y').' a '.$pit->data_final->format('d/m/Y'))

@section('content')
    <div class="grid gap-6">
        <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-card sm:p-8">
            <div class="flex flex-col gap-5 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <a href="{{ route('pits.index') }}" class="text-sm font-bold text-brand-700">← Voltar para PITs</a>
                    <div class="mt-4 flex items-center gap-3"><h2 class="text-3xl font-extrabold text-slate-950">{{ $pit->nome }}</h2><x-plan-status-badge :status="$pit->status" /></div>
                    <p class="mt-2 text-sm font-semibold text-slate-500">{{ $pit->data_inicial->format('d/m/Y') }} a {{ $pit->data_final->format('d/m/Y') }}</p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('pits.edit', $pit) }}" class="inline-flex min-h-11 items-center justify-center rounded-xl border border-slate-200 px-5 text-sm font-bold text-slate-700">Editar PIT</a>
                    <a href="{{ route('pits.plans.create', $pit) }}" class="inline-flex min-h-11 items-center justify-center rounded-xl bg-brand-700 px-5 text-sm font-bold text-white">+ Novo PAT</a>
                </div>
            </div>
        </section>

        <section>
            <div class="mb-4"><p class="text-xs font-extrabold uppercase tracking-[0.16em] text-brand-700">Planos de atividades</p><h2 class="mt-1 text-xl font-extrabold text-slate-950">PATs deste PIT</h2></div>
            @if ($pit->planosTrabalho->isEmpty())
                <div class="rounded-3xl border border-dashed border-brand-300 bg-white px-6 py-14 text-center shadow-card"><h3 class="text-lg font-extrabold text-slate-900">Nenhum PAT cadastrado</h3><p class="mt-2 text-sm text-slate-500">Cadastre o primeiro PAT para organizar as atividades deste PIT.</p></div>
            @else
                <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-3">@foreach ($pit->planosTrabalho as $plan)<x-plan-card :plan="$plan" />@endforeach</div>
            @endif
        </section>

        @if ($pit->planosTrabalho->isEmpty())
            <form method="POST" action="{{ route('pits.destroy', $pit) }}" onsubmit="return confirm('Excluir este PIT?')" class="flex justify-end">@csrf @method('DELETE')<button class="text-sm font-bold text-red-700">Excluir PIT vazio</button></form>
        @endif
    </div>
@endsection
