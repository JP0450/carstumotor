<?php

namespace App\Http\Requests\Finanza;

use Illuminate\Foundation\Http\FormRequest;

class StoreTransaccionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tipo' => ['required', 'string', 'in:ingreso,gasto,impuesto,costo_venta,cuenta_por_pagar,cuenta_por_cobrar'],
            'monto' => ['required', 'numeric', 'min:0'],
            'descripcion' => ['required', 'string', 'min:5', 'max:500'],
            'fecha' => ['required', 'date'],
            'categoria' => ['required', 'string', 'max:50'],
        ];
    }

    public function messages(): array
    {
        return [
            'tipo.in' => 'El tipo de transacción no es válido.',
            'monto.min' => 'El monto debe ser mayor o igual a cero.',
            'descripcion.min' => 'La descripción debe tener al menos 5 caracteres.',
        ];
    }
}
