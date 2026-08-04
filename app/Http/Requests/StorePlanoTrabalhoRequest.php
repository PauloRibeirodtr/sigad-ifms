<?php

namespace App\Http\Requests;

use App\Models\PlanoTrabalho;
use Illuminate\Foundation\Http\FormRequest;

class StorePlanoTrabalhoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', PlanoTrabalho::class);
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
