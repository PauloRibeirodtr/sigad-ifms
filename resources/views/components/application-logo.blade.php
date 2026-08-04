@props([
    'compact' => false,
    'inverted' => false,
])

<div {{ $attributes->class(['flex items-center gap-3']) }}>
    <span @class([
        'grid shrink-0 place-items-center rounded-2xl',
        'size-11' => ! $compact,
        'size-10' => $compact,
        'bg-white/12 text-white ring-1 ring-white/20' => $inverted,
        'bg-brand-50 text-brand-700 ring-1 ring-brand-100' => ! $inverted,
    ])>
        <svg viewBox="0 0 48 48" class="size-8" aria-hidden="true">
            <rect x="8" y="7" width="5" height="5" rx="1" fill="currentColor" opacity=".65"/>
            <rect x="15" y="3" width="6" height="6" rx="1" fill="currentColor" opacity=".4"/>
            <path d="M7 17.5c6.5-.8 12.2.8 17 4.7 4.8-3.9 10.5-5.5 17-4.7v21c-6.2-.6-11.8 1-17 4.7-5.2-3.7-10.8-5.3-17-4.7v-21Z" fill="currentColor" opacity=".2"/>
            <path d="M7 17.5c6.5-.8 12.2.8 17 4.7v21c-5.2-3.7-10.8-5.3-17-4.7v-21Zm34 0c-6.5-.8-12.2.8-17 4.7v21c5.2-3.7 10.8-5.3 17-4.7v-21Z" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linejoin="round"/>
            <path d="m15 25 5.2 5.2L34 16.4" fill="none" stroke="currentColor" stroke-width="3.2" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
    </span>

    @unless ($compact)
        <span class="min-w-0 leading-none">
            <span @class([
                'block text-xl font-extrabold tracking-[0.16em]',
                'text-white' => $inverted,
                'text-slate-900' => ! $inverted,
            ])>SIGAD</span>
            <span @class([
                'mt-1 block text-[0.62rem] font-medium tracking-wide',
                'text-white/70' => $inverted,
                'text-slate-500' => ! $inverted,
            ])>Gestão de Atividades Docentes</span>
        </span>
    @endunless
</div>
