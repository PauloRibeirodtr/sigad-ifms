@props(['status'])

@php
    $classes = match ($status) {
        \App\Enums\AtividadeStatus::Aberta => 'bg-slate-100 text-slate-700 ring-slate-200',
        \App\Enums\AtividadeStatus::EmAndamento => 'bg-brand-50 text-brand-700 ring-brand-100',
        \App\Enums\AtividadeStatus::Aguardando => 'bg-warning-50 text-warning-700 ring-warning-100',
        \App\Enums\AtividadeStatus::Concluida => 'bg-emerald-50 text-emerald-700 ring-emerald-100',
        \App\Enums\AtividadeStatus::Cancelada => 'bg-red-50 text-red-700 ring-red-100',
    };
@endphp

<span {{ $attributes->class(['inline-flex rounded-full px-2.5 py-1 text-xs font-bold ring-1', $classes]) }}>{{ $status->label() }}</span>
