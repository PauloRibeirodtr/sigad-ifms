<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

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
        ];
    }

    public function attributes(): array
    {
        return [
            'nome' => 'nome',
            'descricao' => 'descrição',
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
