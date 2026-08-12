@props(['plan', 'categories', 'activity' => null])

<div class="grid gap-5">
    <div class="grid gap-5 md:grid-cols-2">
        <div class="md:col-span-2"><x-form-input name="titulo" label="Título" :value="$activity?->titulo" required maxlength="255" placeholder="Descreva a atividade de forma objetiva" /></div>

        <div class="grid gap-2">
            <label for="categoria_id" class="text-sm font-semibold text-slate-700">Categoria</label>
            <select id="categoria_id" name="categoria_id" required class="min-h-12 rounded-xl border border-slate-200 bg-white px-4 text-sm text-slate-900 shadow-sm focus:border-brand-500 focus:ring-4 focus:ring-brand-100">
                <option value="">Selecione</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}" @selected((string) old('categoria_id', $activity?->categoria_id) === (string) $category->id)>
                        {{ $category->nome }}{{ isset($category->ativa) && ! $category->ativa ? ' (inativa — categoria atual)' : '' }}
                    </option>
                @endforeach
            </select>
            @error('categoria_id')<p class="text-sm font-medium text-red-600" role="alert">{{ $message }}</p>@enderror
        </div>

        @if ($activity === null)
            @php($selectedStatus = old('status', \App\Enums\AtividadeStatus::Aberta->value))
            <fieldset class="grid gap-2 md:col-span-2">
                <legend class="text-sm font-semibold text-slate-700">Status</legend>
                <div class="grid gap-3 sm:grid-cols-2">
                    @foreach ([\App\Enums\AtividadeStatus::Aberta, \App\Enums\AtividadeStatus::Concluida] as $status)
                        <label class="flex cursor-pointer items-start gap-3 rounded-xl border border-slate-200 bg-white p-4 shadow-sm transition hover:border-brand-300 focus-within:ring-4 focus-within:ring-brand-100 has-checked:border-brand-500 has-checked:bg-brand-50">
                            <input type="radio" name="status" value="{{ $status->value }}" @checked($selectedStatus === $status->value) required class="mt-0.5 size-4 border-slate-300 text-brand-700 focus:ring-brand-500">
                            <span>
                                <span class="block text-sm font-bold text-slate-900">{{ $status->label() }}</span>
                                <span class="mt-1 block text-xs leading-5 text-slate-500">{{ $status === \App\Enums\AtividadeStatus::Aberta ? 'Permanece disponível para movimentações futuras.' : 'Registro pontual que não exige acompanhamento.' }}</span>
                            </span>
                        </label>
                    @endforeach
                </div>
                @error('status')<p class="text-sm font-medium text-red-600" role="alert">{{ $message }}</p>@enderror
            </fieldset>
        @endif

        <x-form-input name="solicitante" label="Solicitante" :value="$activity?->solicitante" maxlength="255" placeholder="Pessoa, setor ou demanda interna" />
        <x-form-input name="data_atividade" label="Data da atividade" type="date" :value="$activity?->data_atividade?->format('Y-m-d')" :min="$plan->data_inicial->format('Y-m-d')" :max="$plan->data_final->format('Y-m-d')" required data-activity-date />

        <div class="grid gap-2">
            <label for="prioridade" class="text-sm font-semibold text-slate-700">Prioridade</label>
            <select id="prioridade" name="prioridade" required class="min-h-12 rounded-xl border border-slate-200 bg-white px-4 text-sm text-slate-900 shadow-sm focus:border-brand-500 focus:ring-4 focus:ring-brand-100">
                @foreach (\App\Enums\AtividadePrioridade::cases() as $priority)
                    <option value="{{ $priority->value }}" @selected(old('prioridade', $activity?->prioridade?->value ?? \App\Enums\AtividadePrioridade::Normal->value) === $priority->value)>{{ $priority->label() }}</option>
                @endforeach
            </select>
            @error('prioridade')<p class="text-sm font-medium text-red-600" role="alert">{{ $message }}</p>@enderror
        </div>

        <x-form-input name="prazo" label="Prazo" type="date" :value="$activity?->prazo?->format('Y-m-d')" data-deadline :data-plan-end="$plan->data_final->format('Y-m-d')" />

        <div class="md:col-span-2"><x-form-input name="proxima_acao" label="Próxima ação" :value="$activity?->proxima_acao" maxlength="2000" placeholder="Ex.: Encaminhar para análise do Colegiado" /></div>
    </div>

    <div data-deadline-warning class="hidden rounded-xl border border-warning-100 bg-warning-50 px-4 py-3 text-sm font-medium text-warning-700">
        O prazo ultrapassa o fim do PIT. O cadastro é permitido, mas revise se a data está correta.
    </div>

    <div class="grid gap-2">
        <div class="flex items-center justify-between gap-3"><label for="descricao" class="text-sm font-semibold text-slate-700">Descrição geral</label><span class="text-xs text-slate-400">Opcional</span></div>
        <textarea id="descricao" name="descricao" rows="4" maxlength="5000" class="w-full resize-y rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 shadow-sm transition placeholder:text-slate-400 focus:border-brand-500 focus:ring-4 focus:ring-brand-100" placeholder="Contexto e objetivo da atividade">{{ old('descricao', $activity?->descricao) }}</textarea>
        @error('descricao')<p class="text-sm font-medium text-red-600" role="alert">{{ $message }}</p>@enderror
    </div>
</div>
