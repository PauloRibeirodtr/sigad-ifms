@props(['category' => null])

<div class="grid gap-5">
    <x-form-input name="nome" label="Nome" :value="$category?->nome" required maxlength="255" placeholder="Ex.: Atendimento discente" />

    <div class="grid gap-2">
        <div class="flex items-center justify-between gap-3">
            <label for="descricao" class="text-sm font-semibold text-slate-700">Descrição</label>
            <span class="text-xs text-slate-400">Opcional</span>
        </div>
        <textarea
            id="descricao"
            name="descricao"
            rows="5"
            maxlength="2000"
            placeholder="Explique quando esta categoria deve ser utilizada"
            {{ $attributes->class([
                'w-full resize-y rounded-xl border bg-white px-4 py-3 text-sm text-slate-900 shadow-sm transition placeholder:text-slate-400',
                'border-red-300 focus:border-red-500 focus:ring-4 focus:ring-red-100' => $errors->has('descricao'),
                'border-slate-200 hover:border-slate-300 focus:border-brand-500 focus:ring-4 focus:ring-brand-100' => ! $errors->has('descricao'),
            ]) }}
        >{{ old('descricao', $category?->descricao) }}</textarea>
        @error('descricao')
            <p class="text-sm font-medium text-red-600" role="alert">{{ $message }}</p>
        @enderror
    </div>
</div>
