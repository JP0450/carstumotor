<?php

namespace App\Http\Requests\Pedido;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProgresoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'progreso' => ['required', 'integer', 'min:0', 'max:100'],
        ];
    }

    public function messages(): array
    {
        return [
            'progreso.required' => 'El progreso es obligatorio.',
            'progreso.integer' => 'El progreso debe ser un número entero.',
            'progreso.min' => 'El progreso mínimo es 0%.',
            'progreso.max' => 'El progreso máximo es 100%.',
        ];
    }
}
