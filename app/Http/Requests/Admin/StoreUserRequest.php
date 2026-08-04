<?php

namespace App\Http\Requests\Admin;

use App\Enums\UserProfile;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', User::class);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique(User::class)],
            'perfil' => ['required', Rule::enum(UserProfile::class)],
            'ativo' => ['required', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.unique' => 'Já existe um usuário cadastrado com este e-mail.',
            'perfil.enum' => 'Selecione um perfil válido.',
            'ativo.boolean' => 'Selecione um status válido.',
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'nome',
            'email' => 'e-mail',
            'perfil' => 'perfil',
            'ativo' => 'status',
        ];
    }

    protected function prepareForValidation(): void
    {
        $data = [
            'email' => $this->string('email')->trim()->lower()->value(),
        ];

        if ($this->has('ativo')) {
            $data['ativo'] = $this->boolean('ativo');
        }

        $this->merge($data);
    }
}
