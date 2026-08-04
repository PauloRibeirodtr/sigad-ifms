@props(['plan' => null, 'editing' => false])

<div class="grid gap-5">
    <x-form-input name="nome" label="Nome" :value="$plan?->nome" required maxlength="255" placeholder="Ex.: Plano de Trabalho 2026.2" />

    <div class="grid gap-2">
        <div class="flex items-center justify-between gap-3">
            <label for="descricao" class="text-sm font-semibold text-slate-700">Descrição</label>
            <span class="text-xs text-slate-400">Opcional</span>
        </div>
        <textarea
            id="descricao"
            name="descricao"
            rows="5"
            maxlength="5000"
            placeholder="Descreva o objetivo e o contexto deste Plano de Trabalho"
            class="w-full resize-y rounded-xl border bg-white px-4 py-3 text-sm text-slate-900 shadow-sm transition placeholder:text-slate-400 {{ $errors->has('descricao') ? 'border-red-300 focus:border-red-500 focus:ring-4 focus:ring-red-100' : 'border-slate-200 hover:border-slate-300 focus:border-brand-500 focus:ring-4 focus:ring-brand-100' }}"
        >{{ old('descricao', $plan?->descricao) }}</textarea>
        @error('descricao')
            <p class="text-sm font-medium text-red-600" role="alert">{{ $message }}</p>
        @enderror
    </div>

    <div class="grid gap-5 sm:grid-cols-2">
        <x-form-input
            name="data_inicial"
            label="Data inicial"
            type="date"
            :value="$plan?->data_inicial?->format('Y-m-d')"
            :max="$editing ? $plan->data_inicial->format('Y-m-d') : null"
            required
        />
        <x-form-input
            name="data_final"
            label="Data final"
            type="date"
            :value="$plan?->data_final?->format('Y-m-d')"
            :min="$editing ? $plan->data_final->format('Y-m-d') : null"
            required
        />
    </div>

    @if ($editing)
        <x-alert type="info">O período existente pode ser mantido ou ampliado. Não é permitido mover o início para frente nem antecipar o término.</x-alert>
    @endif
</div>
