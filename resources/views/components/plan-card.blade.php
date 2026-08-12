@props(['plan'])

<article class="flex min-h-full flex-col overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-card transition hover:-translate-y-0.5 hover:shadow-lg">
    <div class="flex flex-1 flex-col p-5 sm:p-6">
        <div class="flex items-start justify-between gap-4">
            <span class="grid size-12 shrink-0 place-items-center rounded-2xl bg-brand-50 text-brand-700 ring-1 ring-brand-100">
                <svg viewBox="0 0 24 24" class="size-6" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                    <path d="M4 5.5A2.5 2.5 0 0 1 6.5 3H10l2 2h5.5A2.5 2.5 0 0 1 20 7.5v10a2.5 2.5 0 0 1-2.5 2.5h-11A2.5 2.5 0 0 1 4 17.5v-12Z" stroke-linejoin="round"/>
                </svg>
            </span>
            <x-plan-status-badge :status="$plan->status" />
        </div>

        <p class="mt-5 text-xs font-extrabold uppercase tracking-[0.16em] text-brand-700">PAT</p>
        <h3 class="mt-1 text-lg font-extrabold tracking-tight text-slate-950">{{ $plan->nome }}</h3>
        <p class="mt-2 line-clamp-2 min-h-10 text-sm leading-5 text-slate-500">{{ $plan->descricao ?: 'Sem descrição.' }}</p>

        <div class="mt-5 flex items-center gap-2 rounded-xl bg-slate-50 px-3 py-3 text-xs font-semibold text-slate-600">
            <svg viewBox="0 0 24 24" class="size-4 shrink-0 text-slate-400" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                <rect x="3" y="5" width="18" height="16" rx="2"/><path d="M8 3v4m8-4v4M3 10h18"/>
            </svg>
            <span>{{ $plan->data_inicial->format('d/m/Y') }} a {{ $plan->data_final->format('d/m/Y') }}</span>
        </div>

        <dl class="mt-5 grid grid-cols-4 divide-x divide-slate-100 border-y border-slate-100 py-4 text-center">
            <div><dt class="text-[0.65rem] font-bold text-slate-500">Atividades</dt><dd class="mt-1 text-lg font-extrabold text-slate-900">{{ $plan->atividades_count }}</dd></div>
            <div><dt class="text-[0.65rem] font-bold text-slate-500">Aguardando</dt><dd class="mt-1 text-lg font-extrabold text-slate-700">{{ $plan->atividades_aguardando_count }}</dd></div>
            <div><dt class="text-[0.65rem] font-bold text-warning-700">Em andamento</dt><dd class="mt-1 text-lg font-extrabold text-warning-700">{{ $plan->atividades_em_andamento_count }}</dd></div>
            <div><dt class="text-[0.65rem] font-bold text-brand-700">Concluídas</dt><dd class="mt-1 text-lg font-extrabold text-brand-700">{{ $plan->atividades_concluidas_count }}</dd></div>
        </dl>

        @if ($plan->atividades_atrasadas_count || $plan->atividades_urgentes_count || $plan->atividades_sem_atualizacao_count)
            <div class="mt-4 flex flex-wrap gap-2">
                @if ($plan->atividades_atrasadas_count)
                    <a href="{{ route('plans.activities.index', ['plano' => $plan, 'indicador' => 'atrasadas']) }}" class="rounded-full bg-red-50 px-3 py-1.5 text-[0.7rem] font-extrabold text-red-700">{{ $plan->atividades_atrasadas_count }} atrasada(s)</a>
                @endif
                @if ($plan->atividades_urgentes_count)
                    <a href="{{ route('plans.activities.index', ['plano' => $plan, 'indicador' => 'urgentes']) }}" class="rounded-full bg-brand-50 px-3 py-1.5 text-[0.7rem] font-extrabold text-brand-700">{{ $plan->atividades_urgentes_count }} urgente(s)</a>
                @endif
                @if ($plan->atividades_sem_atualizacao_count)
                    <a href="{{ route('plans.activities.index', ['plano' => $plan, 'indicador' => 'sem_atualizacao']) }}" class="rounded-full bg-slate-100 px-3 py-1.5 text-[0.7rem] font-extrabold text-slate-600">{{ $plan->atividades_sem_atualizacao_count }} sem atualização</a>
                @endif
            </div>
        @endif
    </div>

    <div class="grid grid-cols-3 gap-2 border-t border-slate-100 bg-slate-50/60 p-4">
        <a href="{{ route('plans.activities.index', $plan) }}" class="inline-flex min-h-11 items-center justify-center rounded-xl bg-brand-700 px-2 text-xs font-bold text-white transition hover:bg-brand-800 sm:text-sm">Atividades</a>
        <a href="{{ route('plans.show', $plan) }}" class="inline-flex min-h-11 items-center justify-center rounded-xl border border-slate-200 bg-white px-2 text-xs font-bold text-slate-700 transition hover:bg-slate-100 sm:text-sm">PAT</a>
        <a href="{{ route('plans.edit', $plan) }}" class="inline-flex min-h-11 items-center justify-center rounded-xl border border-slate-200 bg-white px-4 text-sm font-bold text-slate-700 transition hover:bg-slate-100">Editar</a>
    </div>
</article>
