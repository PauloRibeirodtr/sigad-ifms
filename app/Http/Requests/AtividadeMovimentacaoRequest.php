<?php

namespace App\Http\Requests;

use App\Enums\AguardandoPor;
use App\Enums\AtividadeStatus;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\File;
use Illuminate\Validation\Validator;

abstract class AtividadeMovimentacaoRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'data_movimentacao' => ['required', 'date_format:Y-m-d'],
            'descricao' => ['required', 'string', 'max:5000'],
            'status' => ['required', Rule::enum(AtividadeStatus::class)],
            'aguardando_por' => [
                'nullable',
                'required_if:status,'.AtividadeStatus::Aguardando->value,
                Rule::enum(AguardandoPor::class),
            ],
            'aguardando_descricao' => [
                'nullable',
                'required_if:status,'.AtividadeStatus::Aguardando->value,
                'string',
                'max:255',
            ],
            'minutos_trabalhados' => ['nullable', 'integer', 'min:1'],
            'anexo' => [
                'nullable',
                File::types(['pdf', 'doc', 'docx', 'xls', 'xlsx', 'odt', 'ods', 'jpg', 'jpeg', 'png', 'zip'])->max('10mb'),
                'extensions:pdf,doc,docx,xls,xlsx,odt,ods,jpg,jpeg,png,zip',
            ],
            'remover_anexo' => ['sometimes', 'boolean'],
        ];
    }

    /**
     * @return array<int, callable>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->has('data_movimentacao')) {
                    return;
                }

                $activity = $this->route('atividade');
                $plan = $this->route('plano');
                $movementDate = CarbonImmutable::createFromFormat('!Y-m-d', $this->string('data_movimentacao')->value());

                if ($movementDate->lessThan($activity->data_atividade)) {
                    $validator->errors()->add('data_movimentacao', 'A movimentação não pode ser anterior à data da atividade.');
                }

                if ($movementDate->lessThan($plan->data_inicial) || $movementDate->greaterThan($plan->data_final)) {
                    $validator->errors()->add('data_movimentacao', sprintf(
                        'A data informada deve estar entre %s e %s, período de vigência do Plano de Trabalho.',
                        $plan->data_inicial->format('d/m/Y'),
                        $plan->data_final->format('d/m/Y'),
                    ));
                }

                $attachment = $this->file('anexo');

                if ($attachment !== null && Str::length($attachment->getClientOriginalName()) > 255) {
                    $validator->errors()->add('anexo', 'O nome original do anexo deve possuir no máximo 255 caracteres.');
                }
            },
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'aguardando_por.required_if' => 'Informe por quem ou pelo que a atividade está aguardando.',
            'aguardando_descricao.required_if' => 'Detalhe quem ou o que está sendo aguardado.',
            'minutos_trabalhados.min' => 'O tempo trabalhado deve ser maior que zero.',
            'anexo.extensions' => 'A extensão do anexo não é permitida.',
            'anexo.max' => 'O anexo deve possuir no máximo 10 MB.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $status = $this->string('status')->value();

        $this->merge([
            'descricao' => $this->string('descricao')->trim()->value(),
            'aguardando_por' => $status === AtividadeStatus::Aguardando->value ? $this->string('aguardando_por')->value() : null,
            'aguardando_descricao' => $status === AtividadeStatus::Aguardando->value
                ? $this->nullableTrimmedString('aguardando_descricao')
                : null,
            'minutos_trabalhados' => $this->filled('minutos_trabalhados') ? $this->input('minutos_trabalhados') : null,
            'remover_anexo' => $this->boolean('remover_anexo'),
        ]);
    }

    private function nullableTrimmedString(string $key): ?string
    {
        $value = $this->string($key)->trim()->value();

        return $value !== '' ? $value : null;
    }
}
