<?php

namespace App\Http\Requests\Cita;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCitaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'vehiculo_id' => [
                'required',
                'integer',
                Rule::exists('vehiculos', 'id')->where(fn ($query) => $query->where('disponible', true)),
            ],
            'fecha_hora' => ['required', 'date', 'after:now'],
            'motivo' => ['required', 'string', 'min:10', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'vehiculo_id.required' => 'Debés indicar el vehículo.',
            'vehiculo_id.exists' => 'El vehículo no está disponible o no existe.',
            'fecha_hora.required' => 'La fecha y hora son obligatorias.',
            'fecha_hora.after' => 'La cita debe programarse para una fecha futura.',
            'motivo.required' => 'El motivo de la cita es obligatorio.',
            'motivo.min' => 'El motivo debe tener al menos 10 caracteres.',
            'motivo.max' => 'El motivo no puede superar los 500 caracteres.',
        ];
    }
}
