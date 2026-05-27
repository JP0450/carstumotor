<?php

namespace App\Http\Requests\Pedido;

use Illuminate\Foundation\Http\FormRequest;

class StorePedidoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'descripcion' => ['required', 'string', 'min:5', 'max:255'],
            'proveedor' => ['required', 'string', 'min:2', 'max:100'],
            'valor' => ['required', 'numeric', 'min:0'],
            'fecha_pedido' => ['required', 'date'],
            'fecha_estimada_llegada' => ['nullable', 'date', 'after_or_equal:fecha_pedido'],
        ];
    }

    public function messages(): array
    {
        return [
            'descripcion.required' => 'La descripción es obligatoria.',
            'proveedor.required' => 'El proveedor es obligatorio.',
            'valor.required' => 'El valor es obligatorio.',
            'fecha_pedido.required' => 'La fecha del pedido es obligatoria.',
            'fecha_estimada_llegada.after_or_equal' => 'La fecha estimada debe ser igual o posterior a la fecha del pedido.',
        ];
    }
}
