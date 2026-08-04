<?php

namespace App\Http\Requests;

use App\Models\PlanoTrabalho;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class RelatorioPlanoTrabalhoIndexRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('viewAny', PlanoTrabalho::class) ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'data_inicial' => ['nullable', 'required_with:data_final', 'date_format:Y-m-d'],
            'data_final' => ['nullable', 'required_with:data_inicial', 'date_format:Y-m-d', 'after_or_equal:data_inicial'],
        ];
    }

    public function messages(): array
    {
        return [
            'data_inicial.required_with' => 'Informe a data inicial da pesquisa.',
            'data_inicial.date_format' => 'Informe uma data inicial válida.',
            'data_final.required_with' => 'Informe a data final da pesquisa.',
            'data_final.date_format' => 'Informe uma data final válida.',
            'data_final.after_or_equal' => 'A data final deve ser igual ou posterior à data inicial.',
        ];
    }
}
