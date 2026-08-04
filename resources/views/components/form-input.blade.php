@props([
    'name',
    'label',
    'type' => 'text',
    'value' => null,
])

<div class="grid gap-2">
    <label for="{{ $name }}" class="text-sm font-semibold text-slate-700">{{ $label }}</label>

    <input
        id="{{ $name }}"
        name="{{ $name }}"
        type="{{ $type }}"
        @if ($type !== 'password') value="{{ old($name, $value) }}" @endif
        {{ $attributes->class([
            'min-h-12 w-full rounded-xl border bg-white px-4 text-sm text-slate-900 shadow-sm transition placeholder:text-slate-400',
            'border-red-300 focus:border-red-500 focus:ring-4 focus:ring-red-100' => $errors->has($name),
            'border-slate-200 hover:border-slate-300 focus:border-brand-500 focus:ring-4 focus:ring-brand-100' => ! $errors->has($name),
        ]) }}
    >

    @error($name)
        <p class="text-sm font-medium text-red-600" role="alert">{{ $message }}</p>
    @enderror
</div>
