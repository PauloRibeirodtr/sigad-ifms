@props([
    'label',
    'value' => 0,
    'tone' => 'brand',
    'href' => null,
])

@php
    $tones = [
        'brand' => ['icon' => 'bg-brand-50 text-brand-700', 'bar' => 'bg-brand-600'],
        'slate' => ['icon' => 'bg-slate-100 text-slate-600', 'bar' => 'bg-slate-500'],
        'warning' => ['icon' => 'bg-warning-50 text-warning-700', 'bar' => 'bg-warning-500'],
        'danger' => ['icon' => 'bg-red-50 text-red-700', 'bar' => 'bg-red-600'],
    ];
    $selectedTone = $tones[$tone] ?? $tones['brand'];
    $classes = 'rounded-2xl border border-slate-200 bg-white p-5 shadow-card transition';
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->class([$classes, 'group hover:-translate-y-0.5 hover:border-brand-200 hover:shadow-lg']) }}>
@else
    <article {{ $attributes->class([$classes]) }}>
@endif
    <div class="flex items-center gap-4">
        <span @class(['grid size-12 shrink-0 place-items-center rounded-2xl', $selectedTone['icon']])>
            {{ $icon }}
        </span>
        <div class="min-w-0 flex-1">
            <p class="truncate text-sm font-semibold text-slate-600">{{ $label }}</p>
            <p class="mt-1 text-3xl font-extrabold tracking-tight text-slate-950">{{ $value }}</p>
        </div>
        @if ($href)
            <span class="text-lg font-bold text-slate-300 transition group-hover:translate-x-0.5 group-hover:text-brand-600" aria-hidden="true">→</span>
        @endif
    </div>
    <div class="mt-5 h-1 overflow-hidden rounded-full bg-slate-100">
        <div @class(['h-full w-1/3 rounded-full', $selectedTone['bar']])></div>
    </div>
@if ($href)
    </a>
@else
    </article>
@endif
