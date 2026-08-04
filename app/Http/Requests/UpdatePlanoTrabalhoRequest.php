<?php

namespace App\Http\Requests;

use Carbon\CarbonImmutable;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class UpdatePlanoTrabalhoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('plano'));
    }

    public function rules(): array
    {
        return [
            'nome' => ['required', 'string', 'max:255'],
            'descricao' => ['nullable', 'string', 'max:5000'],
            'data_inicial' => ['required', 'date_format:Y-m-d'],
            'data_final' => ['required', 'date_format:Y-m-d', 'after_or_equal:data_inicial'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->has('data_inicial') || $validator->errors()->has('data_final')) {
                    return;
                }

                $plan = $this->route('plano');
                $newStartDate = CarbonImmutable::createFromFormat('!Y-m-d', $this->string('data_inicial')->value());
                $newEndDate = CarbonImmutable::createFromFormat('!Y-m-d', $this->string('data_final')->value());

                if ($newStartDate->greaterThan($plan->data_inicial)) {
                    $validator->errors()->add('data_inicial', 'O início não pode ser movido para frente, pois o período do Plano de Trabalho só pode ser ampliado.');
                }

                if ($newEndDate->lessThan($plan->data_final)) {
                    $validator->errors()->add('data_final', 'O término não pode ser antecipado, pois o período do Plano de Trabalho só pode ser ampliado.');
                }
            },
        ];
    }

    public function messages(): array
    {
        return [
            'data_final.after_or_equal' => 'A data final deve ser igual ou posterior à data inicial.',
        ];
    }

    public function attributes(): array
    {
        return [
            'nome' => 'nome',
            'descricao' => 'descrição',
            'data_inicial' => 'data inicial',
            'data_final' => 'data final',
        ];
    }

    protected function prepareForValidation(): void
    {
        $description = $this->string('descricao')->trim()->value();

        $this->merge([
            'nome' => $this->string('nome')->trim()->value(),
            'descricao' => $description !== '' ? $description : null,
        ]);
    }
}
