@props(['plan'])

@php($selectedStatus = old('movimentacao_status', \App\Enums\AtividadeStatus::Aberta->value))

<div class="grid gap-5">
    <div class="grid gap-5 md:grid-cols-2">
        <x-form-input name="data_movimentacao" label="Data da primeira movimentação" type="date" :value="old('data_movimentacao', old('data_atividade'))" :min="$plan->data_inicial->format('Y-m-d')" :max="$plan->data_final->format('Y-m-d')" required data-movement-date />

        <div class="grid gap-2">
            <label for="movimentacao_status" class="text-sm font-semibold text-slate-700">Status após a movimentação</label>
            <select id="movimentacao_status" name="movimentacao_status" required data-activity-status class="min-h-12 rounded-xl border border-slate-200 bg-white px-4 text-sm text-slate-900 shadow-sm focus:border-brand-500 focus:ring-4 focus:ring-brand-100">
                @foreach (\App\Enums\AtividadeStatus::cases() as $status)
                    <option value="{{ $status->value }}" @selected($selectedStatus === $status->value)>{{ $status->label() }}</option>
                @endforeach
            </select>
            @error('movimentacao_status')<p class="text-sm font-medium text-red-600" role="alert">{{ $message }}</p>@enderror
        </div>

        <div class="md:col-span-2 grid gap-2">
            <label for="movimentacao_descricao" class="text-sm font-semibold text-slate-700">Descrição da movimentação</label>
            <textarea id="movimentacao_descricao" name="movimentacao_descricao" rows="4" maxlength="5000" required class="w-full resize-y rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 shadow-sm transition placeholder:text-slate-400 focus:border-brand-500 focus:ring-4 focus:ring-brand-100" placeholder="Registre o que foi realizado nesta primeira ação">{{ old('movimentacao_descricao') }}</textarea>
            @error('movimentacao_descricao')<p class="text-sm font-medium text-red-600" role="alert">{{ $message }}</p>@enderror
        </div>

        <div data-waiting-fields class="{{ $selectedStatus === \App\Enums\AtividadeStatus::Aguardando->value ? 'contents' : 'hidden' }}">
            <div class="grid gap-2">
                <label for="aguardando_por" class="text-sm font-semibold text-slate-700">Aguardando por</label>
                <select id="aguardando_por" name="aguardando_por" class="min-h-12 rounded-xl border border-slate-200 bg-white px-4 text-sm text-slate-900 shadow-sm focus:border-brand-500 focus:ring-4 focus:ring-brand-100">
                    <option value="">Selecione</option>
                    @foreach (\App\Enums\AguardandoPor::cases() as $waitingFor)
                        <option value="{{ $waitingFor->value }}" @selected(old('aguardando_por') === $waitingFor->value)>{{ $waitingFor->label() }}</option>
                    @endforeach
                </select>
                @error('aguardando_por')<p class="text-sm font-medium text-red-600" role="alert">{{ $message }}</p>@enderror
            </div>
            <x-form-input name="aguardando_descricao" label="Detalhamento da espera" :value="old('aguardando_descricao')" maxlength="255" placeholder="Ex.: COGEA" />
        </div>

        <x-form-input name="minutos_trabalhados" label="Minutos trabalhados" type="number" :value="old('minutos_trabalhados')" min="1" step="1" placeholder="Opcional" />

        <div class="grid gap-2 md:col-span-2">
            <label for="anexo" class="text-sm font-semibold text-slate-700">Anexo</label>
            <input id="anexo" name="anexo" type="file" accept=".pdf,.doc,.docx,.xls,.xlsx,.odt,.ods,.jpg,.jpeg,.png,.zip" class="min-h-12 w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-700 shadow-sm file:mr-4 file:rounded-lg file:border-0 file:bg-brand-50 file:px-4 file:py-2 file:font-bold file:text-brand-700 hover:file:bg-brand-100">
            <p class="text-xs leading-5 text-slate-500">Opcional, um arquivo de até 10 MB. O armazenamento é privado.</p>
            @error('anexo')<p class="text-sm font-medium text-red-600" role="alert">{{ $message }}</p>@enderror
        </div>
    </div>
</div>
