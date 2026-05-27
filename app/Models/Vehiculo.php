<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Vehiculo extends Model
{
    use HasFactory;

    protected $fillable = [
        'marca',
        'modelo',
        'color',
        'puertas',
        'hp',
        'imagen',
        'precio_cliente',
        'costo_empresa',
        'disponible',
    ];

    protected function casts(): array
    {
        return [
            'precio_cliente' => 'decimal:2',
            'costo_empresa' => 'decimal:2',
            'disponible' => 'boolean',
        ];
    }

    public function inventario(): HasOne
    {
        return $this->hasOne(Inventario::class);
    }
}
