<?php

namespace App\Http\Requests\Finanza;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MovimientoFinancieroRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'tipo' => ['required', Rule::in(['ingreso', 'gasto', 'impuesto', 'costo_venta'])],
            'monto' => ['required', 'numeric', 'gt:0'],
            'moneda' => ['required', Rule::in(['COP', 'USD', 'EUR'])],
            'vehiculo_id' => [
                Rule::requiredIf(fn (): bool => $this->input('tipo') === 'ingreso'
                    && $this->input('categoria') === 'Venta de vehículo'),
                'nullable',
                'integer',
                'exists:vehiculos,id',
            ],
            'descripcion' => ['required', 'string', 'min:5', 'max:500'],
            'fecha' => ['required', 'date'],
            'categoria' => ['required', 'string', 'max:50'],
        ];
    }

    public function messages(): array
    {
        return [
            'tipo.in' => 'Selecciona un tipo de movimiento válido.',
            'monto.gt' => 'El monto debe ser mayor que cero.',
            'moneda.required' => 'Selecciona la moneda del movimiento.',
            'descripcion.min' => 'La descripción debe tener al menos 5 caracteres.',
        ];
    }
}
