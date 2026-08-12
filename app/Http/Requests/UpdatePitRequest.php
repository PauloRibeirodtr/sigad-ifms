<?php

namespace App\Http\Requests;

use Carbon\CarbonImmutable;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdatePitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('pit'));
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

                $pit = $this->route('pit');
                $startDate = CarbonImmutable::createFromFormat('!Y-m-d', $this->string('data_inicial')->value());
                $endDate = CarbonImmutable::createFromFormat('!Y-m-d', $this->string('data_final')->value());
                $overlaps = $this->user()->pits()
                    ->whereKeyNot($pit->getKey())
                    ->whereDate('data_inicial', '<=', $endDate)
                    ->whereDate('data_final', '>=', $startDate)
                    ->exists();

                if ($overlaps) {
                    $validator->errors()->add('data_inicial', 'O período informado conflita com outro PIT cadastrado.');
                }

                if ($pit->planosTrabalho()->whereHas('atividades', fn ($activities) => $activities
                    ->whereDate('data_atividade', '<', $startDate))->exists()
                    || $pit->planosTrabalho()->whereHas('atividades.movimentacoes', fn ($movements) => $movements
                        ->whereDate('data_movimentacao', '<', $startDate))->exists()) {
                    $validator->errors()->add('data_inicial', 'A data inicial não pode deixar atividades ou movimentações existentes fora do período do PIT.');
                }

                if ($pit->planosTrabalho()->whereHas('atividades', fn ($activities) => $activities
                    ->whereDate('data_atividade', '>', $endDate))->exists()
                    || $pit->planosTrabalho()->whereHas('atividades.movimentacoes', fn ($movements) => $movements
                        ->whereDate('data_movimentacao', '>', $endDate))->exists()) {
                    $validator->errors()->add('data_final', 'A data final não pode deixar atividades ou movimentações existentes fora do período do PIT.');
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
