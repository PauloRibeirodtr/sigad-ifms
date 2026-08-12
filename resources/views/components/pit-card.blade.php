@props(['pit'])

<article class="flex min-h-full flex-col overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-card transition hover:-translate-y-0.5 hover:shadow-lg">
    <div class="flex flex-1 flex-col p-5 sm:p-6">
        <div class="flex items-start justify-between gap-4">
            <span class="grid size-12 shrink-0 place-items-center rounded-2xl bg-brand-50 text-lg font-extrabold text-brand-700 ring-1 ring-brand-100">{{ $pit->semestre }}º</span>
            <x-plan-status-badge :status="$pit->status" />
        </div>

        <p class="mt-5 text-xs font-extrabold uppercase tracking-[0.16em] text-brand-700">PIT</p>
        <h3 class="mt-1 text-2xl font-extrabold tracking-tight text-slate-950">{{ $pit->nome }}</h3>
        <p class="mt-2 text-sm font-semibold text-slate-500">{{ $pit->data_inicial->format('d/m/Y') }} a {{ $pit->data_final->format('d/m/Y') }}</p>

        <dl class="mt-5 grid grid-cols-2 gap-3 border-y border-slate-100 py-4 text-center">
            <div><dt class="text-xs font-bold text-slate-500">PATs</dt><dd class="mt-1 text-xl font-extrabold text-slate-900">{{ $pit->planos_trabalho_count }}</dd></div>
            <div><dt class="text-xs font-bold text-slate-500">Atividades</dt><dd class="mt-1 text-xl font-extrabold text-brand-700">{{ $pit->planosTrabalho->sum('atividades_count') }}</dd></div>
        </dl>
    </div>

    <div class="grid grid-cols-2 gap-2 border-t border-slate-100 bg-slate-50/60 p-4">
        <a href="{{ route('pits.show', $pit) }}" class="inline-flex min-h-11 items-center justify-center rounded-xl bg-brand-700 px-4 text-sm font-bold text-white transition hover:bg-brand-800">Abrir PIT</a>
        <a href="{{ route('pits.edit', $pit) }}" class="inline-flex min-h-11 items-center justify-center rounded-xl border border-slate-200 bg-white px-4 text-sm font-bold text-slate-700 transition hover:bg-slate-100">Editar</a>
    </div>
</article>
