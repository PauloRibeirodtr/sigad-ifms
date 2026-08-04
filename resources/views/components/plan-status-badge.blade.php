@props(['status'])

@php
    $classes = match ($status) {
        \App\Enums\PlanoTrabalhoStatus::Aguardando => 'bg-warning-50 text-warning-700 ring-warning-100',
        \App\Enums\PlanoTrabalhoStatus::EmAndamento => 'bg-brand-50 text-brand-700 ring-brand-100',
        \App\Enums\PlanoTrabalhoStatus::Encerrado => 'bg-slate-100 text-slate-700 ring-slate-200',
    };
@endphp

<span {{ $attributes->class(['inline-flex rounded-full px-3 py-1 text-xs font-bold ring-1', $classes]) }}>
    {{ $status->label() }}
</span>
