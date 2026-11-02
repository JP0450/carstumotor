<?php

namespace App\Http\Requests\Cita;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;

class StoreCompraCitaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole(User::ROLE_EXTERNO) ?? false;
    }

    public function rules(): array
    {
        return [
            'nombre_contacto' => ['required', 'string', 'max:255'],
            'telefono_contacto' => ['required', 'string', 'max:30', 'regex:/^[0-9+\s().-]+$/'],
            'fecha_hora' => ['required', 'date', 'after:now'],
            'motivo' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'nombre_contacto.required' => 'Indica el nombre de la persona de contacto.',
            'telefono_contacto.required' => 'Indica un teléfono para que podamos contactarte.',
            'telefono_contacto.regex' => 'El teléfono contiene caracteres no válidos.',
            'fecha_hora.required' => 'Selecciona la fecha y hora preferidas para la asesoría.',
            'fecha_hora.after' => 'La asesoría debe programarse para una fecha futura.',
        ];
    }
}
