@props(['type' => 'success'])

<div {{ $attributes->class([
    'rounded-xl border px-4 py-3 text-sm font-medium',
    'border-brand-200 bg-brand-50 text-brand-800' => $type === 'success',
    'border-red-200 bg-red-50 text-red-800' => $type === 'error',
    'border-sky-200 bg-sky-50 text-sky-800' => $type === 'info',
]) }} role="status">
    {{ $slot }}
</div>
