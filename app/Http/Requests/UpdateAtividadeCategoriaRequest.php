<?php

namespace App\Http\Requests;

use App\Models\AtividadeCategoria;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAtividadeCategoriaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('categoria'));
    }

    public function rules(): array
    {
        return [
            'nome' => [
                'required',
                'string',
                'max:255',
                Rule::unique(AtividadeCategoria::class, 'nome')
                    ->where(fn ($query) => $query->where('user_id', $this->user()->getKey()))
                    ->ignore($this->route('categoria')),
            ],
            'descricao' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function messages(): array
    {
        return [
            'nome.unique' => 'Você já possui uma categoria com este nome.',
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
