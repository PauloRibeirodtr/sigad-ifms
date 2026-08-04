@props(['plan', 'activity' => null, 'movement' => null])

@php
    $selectedStatus = old('status', $movement?->status->value ?? \App\Enums\AtividadeStatus::EmAndamento->value);
    $suggestedDate = min(now()->format('Y-m-d'), $plan->data_final->format('Y-m-d'));
    $suggestedDate = $activity ? max($activity->data_atividade->format('Y-m-d'), $suggestedDate) : $suggestedDate;
    $date = old('data_movimentacao', $movement?->data_movimentacao?->format('Y-m-d') ?? $suggestedDate);
@endphp

<div class="grid gap-5 md:grid-cols-2">
    <x-form-input name="data_movimentacao" label="Data da movimentação" type="date" :value="$date" :min="$plan->data_inicial->format('Y-m-d')" :max="$plan->data_final->format('Y-m-d')" required />

    <div class="grid gap-2">
        <label for="status" class="text-sm font-semibold text-slate-700">Status após a movimentação</label>
        <select id="status" name="status" required data-activity-status class="min-h-12 rounded-xl border border-slate-200 bg-white px-4 text-sm text-slate-900 shadow-sm focus:border-brand-500 focus:ring-4 focus:ring-brand-100">
            @foreach (\App\Enums\AtividadeStatus::cases() as $status)
                <option value="{{ $status->value }}" @selected($selectedStatus === $status->value)>{{ $status->label() }}</option>
            @endforeach
        </select>
        @error('status')<p class="text-sm font-medium text-red-600" role="alert">{{ $message }}</p>@enderror
    </div>

    <div class="grid gap-2 md:col-span-2">
        <label for="descricao" class="text-sm font-semibold text-slate-700">Descrição da movimentação</label>
        <textarea id="descricao" name="descricao" rows="5" maxlength="5000" required class="w-full resize-y rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 shadow-sm transition placeholder:text-slate-400 focus:border-brand-500 focus:ring-4 focus:ring-brand-100" placeholder="Descreva a ação efetivamente realizada">{{ old('descricao', $movement?->descricao) }}</textarea>
        @error('descricao')<p class="text-sm font-medium text-red-600" role="alert">{{ $message }}</p>@enderror
    </div>

    <div data-waiting-fields class="{{ $selectedStatus === \App\Enums\AtividadeStatus::Aguardando->value ? 'contents' : 'hidden' }}">
        <div class="grid gap-2">
            <label for="aguardando_por" class="text-sm font-semibold text-slate-700">Aguardando por</label>
            <select id="aguardando_por" name="aguardando_por" class="min-h-12 rounded-xl border border-slate-200 bg-white px-4 text-sm text-slate-900 shadow-sm focus:border-brand-500 focus:ring-4 focus:ring-brand-100">
                <option value="">Selecione</option>
                @foreach (\App\Enums\AguardandoPor::cases() as $waitingFor)
                    <option value="{{ $waitingFor->value }}" @selected(old('aguardando_por', $movement?->aguardando_por?->value) === $waitingFor->value)>{{ $waitingFor->label() }}</option>
                @endforeach
            </select>
            @error('aguardando_por')<p class="text-sm font-medium text-red-600" role="alert">{{ $message }}</p>@enderror
        </div>
        <x-form-input name="aguardando_descricao" label="Detalhamento da espera" :value="old('aguardando_descricao', $movement?->aguardando_descricao)" maxlength="255" placeholder="Ex.: COGEA" />
    </div>

    <x-form-input name="minutos_trabalhados" label="Minutos trabalhados" type="number" :value="old('minutos_trabalhados', $movement?->minutos_trabalhados)" min="1" step="1" placeholder="Opcional" />

    <div class="grid gap-2 md:col-span-2">
        <label for="anexo" class="text-sm font-semibold text-slate-700">Anexo</label>
        <input id="anexo" name="anexo" type="file" accept=".pdf,.doc,.docx,.xls,.xlsx,.odt,.ods,.jpg,.jpeg,.png,.zip" class="min-h-12 w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-700 shadow-sm file:mr-4 file:rounded-lg file:border-0 file:bg-brand-50 file:px-4 file:py-2 file:font-bold file:text-brand-700 hover:file:bg-brand-100">
        <p class="text-xs leading-5 text-slate-500">Um arquivo de até 10 MB: PDF, documentos, planilhas, imagens ou ZIP.</p>
        @error('anexo')<p class="text-sm font-medium text-red-600" role="alert">{{ $message }}</p>@enderror

        @if ($movement?->anexo_nome_original)
            <div class="flex flex-col gap-3 rounded-xl border border-brand-100 bg-brand-50 p-4 sm:flex-row sm:items-center sm:justify-between">
                <p class="min-w-0 truncate text-sm font-semibold text-brand-900">Atual: {{ $movement->anexo_nome_original }}</p>
                <label class="inline-flex items-center gap-2 text-sm font-bold text-red-700">
                    <input type="checkbox" name="remover_anexo" value="1" @checked(old('remover_anexo')) class="rounded border-slate-300 text-red-600 focus:ring-red-200">
                    Remover sem substituir
                </label>
            </div>
        @endif
    </div>
</div>
