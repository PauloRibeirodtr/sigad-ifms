<?php

namespace App\Http\Requests;

use App\Enums\AtividadePrioridade;
use App\Models\AtividadeCategoria;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateAtividadeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('atividade'))
            && $this->user()->can('view', $this->route('plano'));
    }

    public function rules(): array
    {
        $activity = $this->route('atividade');

        return [
            'categoria_id' => [
                'required',
                'integer',
                Rule::exists(AtividadeCategoria::class, 'id')->where(fn ($query) => $query
                    ->where('user_id', $this->user()->getKey())
                    ->where(fn ($categories) => $categories
                        ->where('ativa', true)
                        ->orWhere('id', $activity->categoria_id))),
            ],
            'titulo' => ['required', 'string', 'max:255'],
            'descricao' => ['nullable', 'string', 'max:5000'],
            'solicitante' => ['nullable', 'string', 'max:255'],
            'data_atividade' => ['required', 'date_format:Y-m-d'],
            'prioridade' => ['required', Rule::enum(AtividadePrioridade::class)],
            'prazo' => ['nullable', 'date_format:Y-m-d'],
            'proxima_acao' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->has('data_atividade')) {
                    return;
                }

                $plan = $this->route('plano');
                $activity = $this->route('atividade');
                $activityDate = CarbonImmutable::createFromFormat('!Y-m-d', $this->string('data_atividade')->value());

                if ($activityDate->lessThan($plan->data_inicial) || $activityDate->greaterThan($plan->data_final)) {
                    $validator->errors()->add('data_atividade', sprintf(
                        'A data informada deve estar entre %s e %s, período de vigência do PIT.',
                        $plan->data_inicial->format('d/m/Y'),
                        $plan->data_final->format('d/m/Y'),
                    ));
                }

                $firstMovementDate = $activity->movimentacoes()->oldest('data_movimentacao')->value('data_movimentacao');

                if ($firstMovementDate !== null && $activityDate->greaterThan(CarbonImmutable::parse($firstMovementDate))) {
                    $validator->errors()->add('data_atividade', 'A data da atividade não pode ser posterior à primeira movimentação existente.');
                }
            },
        ];
    }

    public function messages(): array
    {
        return [
            'categoria_id.exists' => 'Selecione uma categoria ativa pertencente à sua conta ou mantenha a categoria atual.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'titulo' => $this->string('titulo')->trim()->value(),
            'descricao' => $this->nullableTrimmedString('descricao'),
            'solicitante' => $this->nullableTrimmedString('solicitante'),
            'prazo' => $this->filled('prazo') ? $this->string('prazo')->value() : null,
            'proxima_acao' => $this->nullableTrimmedString('proxima_acao'),
        ]);
    }

    private function nullableTrimmedString(string $key): ?string
    {
        $value = $this->string($key)->trim()->value();

        return $value !== '' ? $value : null;
    }
}
