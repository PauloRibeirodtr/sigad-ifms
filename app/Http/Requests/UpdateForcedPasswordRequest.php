<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class UpdateForcedPasswordRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->must_change_password === true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'current_password' => ['required', 'string', 'current_password:web'],
            'password' => [
                'required',
                'string',
                'different:current_password',
                Password::default(),
                'confirmed',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'current_password.current_password' => 'A senha atual informada não está correta.',
            'password.different' => 'A nova senha deve ser diferente da senha temporária.',
        ];
    }
}
