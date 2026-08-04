@props(['priority'])

@php
    $classes = match ($priority) {
        \App\Enums\AtividadePrioridade::Baixa => 'text-slate-600',
        \App\Enums\AtividadePrioridade::Normal => 'text-slate-700',
        \App\Enums\AtividadePrioridade::Alta => 'text-warning-700',
        \App\Enums\AtividadePrioridade::Urgente => 'text-red-700',
    };
@endphp

<span {{ $attributes->class(['inline-flex items-center gap-1.5 text-xs font-extrabold', $classes]) }}>
    <span class="size-2 rounded-full bg-current"></span>{{ $priority->label() }}
</span>
