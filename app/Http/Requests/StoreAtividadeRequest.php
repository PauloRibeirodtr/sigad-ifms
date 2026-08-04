<?php

namespace App\Http\Requests;

use App\Enums\AguardandoPor;
use App\Enums\AtividadePrioridade;
use App\Enums\AtividadeStatus;
use App\Models\Atividade;
use App\Models\AtividadeCategoria;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\File;
use Illuminate\Validation\Validator;

class StoreAtividadeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Atividade::class)
            && $this->user()->can('view', $this->route('plano'));
    }

    public function rules(): array
    {
        return [
            'categoria_id' => [
                'required',
                'integer',
                Rule::exists(AtividadeCategoria::class, 'id')->where(fn ($query) => $query
                    ->where('user_id', $this->user()->getKey())
                    ->where('ativa', true)),
            ],
            'titulo' => ['required', 'string', 'max:255'],
            'descricao' => ['nullable', 'string', 'max:5000'],
            'solicitante' => ['nullable', 'string', 'max:255'],
            'data_atividade' => ['required', 'date_format:Y-m-d'],
            'prioridade' => ['required', Rule::enum(AtividadePrioridade::class)],
            'prazo' => ['nullable', 'date_format:Y-m-d'],
            'proxima_acao' => ['nullable', 'string', 'max:2000'],
            'data_movimentacao' => ['required', 'date_format:Y-m-d'],
            'movimentacao_descricao' => ['required', 'string', 'max:5000'],
            'movimentacao_status' => ['required', Rule::enum(AtividadeStatus::class)],
            'aguardando_por' => [
                'nullable',
                'required_if:movimentacao_status,'.AtividadeStatus::Aguardando->value,
                Rule::enum(AguardandoPor::class),
            ],
            'aguardando_descricao' => [
                'nullable',
                'required_if:movimentacao_status,'.AtividadeStatus::Aguardando->value,
                'string',
                'max:255',
            ],
            'minutos_trabalhados' => ['nullable', 'integer', 'min:1'],
            'anexo' => [
                'nullable',
                File::types(['pdf', 'doc', 'docx', 'xls', 'xlsx', 'odt', 'ods', 'jpg', 'jpeg', 'png', 'zip'])->max('10mb'),
                'extensions:pdf,doc,docx,xls,xlsx,odt,ods,jpg,jpeg,png,zip',
            ],
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
                $activityDate = CarbonImmutable::createFromFormat('!Y-m-d', $this->string('data_atividade')->value());

                if ($activityDate->lessThan($plan->data_inicial) || $activityDate->greaterThan($plan->data_final)) {
                    $validator->errors()->add('data_atividade', $this->periodMessage());
                }

                if ($validator->errors()->has('data_movimentacao')) {
                    return;
                }

                $movementDate = CarbonImmutable::createFromFormat('!Y-m-d', $this->string('data_movimentacao')->value());

                if ($movementDate->lessThan($activityDate)) {
                    $validator->errors()->add('data_movimentacao', 'A primeira movimentação não pode ser anterior à data da atividade.');
                }

                if ($movementDate->lessThan($plan->data_inicial) || $movementDate->greaterThan($plan->data_final)) {
                    $validator->errors()->add('data_movimentacao', $this->periodMessage());
                }
            },
            function (Validator $validator): void {
                $attachment = $this->file('anexo');

                if ($attachment !== null && Str::length($attachment->getClientOriginalName()) > 255) {
                    $validator->errors()->add('anexo', 'O nome original do anexo deve possuir no máximo 255 caracteres.');
                }
            },
        ];
    }

    public function messages(): array
    {
        return [
            'categoria_id.exists' => 'Selecione uma categoria ativa pertencente à sua conta.',
            'aguardando_por.required_if' => 'Informe por quem ou pelo que a atividade está aguardando.',
            'aguardando_descricao.required_if' => 'Detalhe quem ou o que está sendo aguardado.',
            'minutos_trabalhados.min' => 'O tempo trabalhado deve ser maior que zero.',
            'anexo.extensions' => 'A extensão do anexo não é permitida.',
            'anexo.max' => 'O anexo deve possuir no máximo 10 MB.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $status = $this->string('movimentacao_status')->value();

        $this->merge([
            'titulo' => $this->string('titulo')->trim()->value(),
            'descricao' => $this->nullableTrimmedString('descricao'),
            'solicitante' => $this->nullableTrimmedString('solicitante'),
            'prioridade' => $this->filled('prioridade') ? $this->string('prioridade')->value() : AtividadePrioridade::Normal->value,
            'prazo' => $this->filled('prazo') ? $this->string('prazo')->value() : null,
            'proxima_acao' => $this->nullableTrimmedString('proxima_acao'),
            'data_movimentacao' => $this->filled('data_movimentacao') ? $this->string('data_movimentacao')->value() : $this->string('data_atividade')->value(),
            'movimentacao_descricao' => $this->string('movimentacao_descricao')->trim()->value(),
            'aguardando_por' => $status === AtividadeStatus::Aguardando->value ? $this->string('aguardando_por')->value() : null,
            'aguardando_descricao' => $status === AtividadeStatus::Aguardando->value ? $this->nullableTrimmedString('aguardando_descricao') : null,
            'minutos_trabalhados' => $this->filled('minutos_trabalhados') ? $this->input('minutos_trabalhados') : null,
        ]);
    }

    private function nullableTrimmedString(string $key): ?string
    {
        $value = $this->string($key)->trim()->value();

        return $value !== '' ? $value : null;
    }

    private function periodMessage(): string
    {
        $plan = $this->route('plano');

        return sprintf(
            'A data informada deve estar entre %s e %s, período de vigência do Plano de Trabalho.',
            $plan->data_inicial->format('d/m/Y'),
            $plan->data_final->format('d/m/Y'),
        );
    }
}
