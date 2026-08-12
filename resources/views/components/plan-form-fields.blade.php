@props(['plan' => null, 'pit' => null])

<div class="grid gap-5">
    <x-form-input name="nome" label="Nome do PAT" :value="$plan?->nome" required maxlength="255" placeholder="Ex.: Ensino, pesquisa e extensão" />

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
            placeholder="Descreva o objetivo e o contexto deste PAT"
            class="w-full resize-y rounded-xl border bg-white px-4 py-3 text-sm text-slate-900 shadow-sm transition placeholder:text-slate-400 {{ $errors->has('descricao') ? 'border-red-300 focus:border-red-500 focus:ring-4 focus:ring-red-100' : 'border-slate-200 hover:border-slate-300 focus:border-brand-500 focus:ring-4 focus:ring-brand-100' }}"
        >{{ old('descricao', $plan?->descricao) }}</textarea>
        @error('descricao')
            <p class="text-sm font-medium text-red-600" role="alert">{{ $message }}</p>
        @enderror
    </div>

    @php($periodPit = $pit ?? $plan?->pit)
    <x-alert type="info">Este PAT utiliza a vigência do PIT {{ $periodPit->nome }}: <strong>{{ $periodPit->data_inicial->format('d/m/Y') }} a {{ $periodPit->data_final->format('d/m/Y') }}</strong>.</x-alert>
</div>
