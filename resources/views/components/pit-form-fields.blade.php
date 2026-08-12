@props(['pit' => null, 'editing' => false])

<div class="grid gap-5">
    <div class="grid gap-5 sm:grid-cols-2">
        <x-form-input name="ano" label="Ano" type="number" :value="$pit?->ano ?? now()->year" min="2000" max="2100" required />

        <div class="grid gap-2">
            <label for="semestre" class="text-sm font-semibold text-slate-700">Semestre</label>
            <select id="semestre" name="semestre" required class="min-h-12 rounded-xl border border-slate-200 bg-white px-4 text-sm text-slate-900 shadow-sm focus:border-brand-500 focus:ring-4 focus:ring-brand-100">
                <option value="1" @selected((int) old('semestre', $pit?->semestre ?? 1) === 1)>1º semestre</option>
                <option value="2" @selected((int) old('semestre', $pit?->semestre ?? 1) === 2)>2º semestre</option>
            </select>
            @error('semestre')<p class="text-sm font-medium text-red-600" role="alert">{{ $message }}</p>@enderror
        </div>
    </div>

    <div class="grid gap-5 sm:grid-cols-2">
        <x-form-input name="data_inicial" label="Data inicial" type="date" :value="$pit?->data_inicial?->format('Y-m-d')" required />
        <x-form-input name="data_final" label="Data final" type="date" :value="$pit?->data_final?->format('Y-m-d')" required />
    </div>

    @if ($editing)
        <x-alert type="info">O período pode ser ampliado ou reduzido, desde que nenhuma atividade ou movimentação existente fique fora da nova vigência.</x-alert>
    @endif
</div>
