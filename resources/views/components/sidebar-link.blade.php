@props([
    'href' => '#',
    'active' => false,
    'disabled' => false,
])

@php
    $classes = [
        'group flex min-h-11 items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-semibold transition',
        'bg-brand-50 text-brand-800 ring-1 ring-brand-100' => $active,
        'text-slate-600 hover:bg-slate-100 hover:text-slate-950' => ! $active && ! $disabled,
        'cursor-not-allowed text-slate-400' => $disabled,
    ];
@endphp

@if ($disabled)
    <span {{ $attributes->class($classes) }} aria-disabled="true">
        <span class="grid size-6 shrink-0 place-items-center text-current">{{ $icon }}</span>
        <span class="min-w-0 flex-1">{{ $slot }}</span>
        <span class="rounded-full bg-slate-100 px-2 py-0.5 text-[0.62rem] font-bold uppercase tracking-wide text-slate-400">Em breve</span>
    </span>
@else
    <a href="{{ $href }}" {{ $attributes->class($classes) }} @if ($active) aria-current="page" @endif>
        <span class="grid size-6 shrink-0 place-items-center text-current">{{ $icon }}</span>
        <span class="min-w-0 flex-1">{{ $slot }}</span>
    </a>
@endif
