<?php

namespace App\Http\Requests;

use App\Models\Pit;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StorePitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Pit::class);
    }

    public function rules(): array
    {
        return [
            'ano' => ['required', 'integer', 'between:2000,2100'],
            'semestre' => ['required', 'integer', Rule::in([1, 2])],
            'data_inicial' => ['required', 'date_format:Y-m-d'],
            'data_final' => ['required', 'date_format:Y-m-d', 'after_or_equal:data_inicial'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->hasAny(['data_inicial', 'data_final'])) {
                    return;
                }

                $startDate = CarbonImmutable::createFromFormat('!Y-m-d', $this->string('data_inicial')->value());
                $endDate = CarbonImmutable::createFromFormat('!Y-m-d', $this->string('data_final')->value());
                $overlaps = $this->user()->pits()
                    ->whereDate('data_inicial', '<=', $endDate)
                    ->whereDate('data_final', '>=', $startDate)
                    ->exists();

                if ($overlaps) {
                    $validator->errors()->add('data_inicial', 'O período informado conflita com outro PIT cadastrado.');
                }
            },
        ];
    }

    public function messages(): array
    {
        return [
            'ano.between' => 'Informe um ano entre 2000 e 2100.',
            'semestre.in' => 'Selecione o primeiro ou o segundo semestre.',
            'data_final.after_or_equal' => 'A data final deve ser igual ou posterior à data inicial.',
        ];
    }
}
