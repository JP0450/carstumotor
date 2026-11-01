<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VehiculoInformacionInternacional extends Model
{
    protected $table = 'vehiculo_informacion_internacional';

    protected $fillable = [
        'anio',
        'version',
        'vin',
        'numero_motor',
        'tipo_vehiculo',
        'kilometraje',
        'combustible',
        'estado',
        'proveedor_internacional',
        'pais_compra',
        'precio_adquisicion',
        'moneda',
        'fecha_compra',
        'incoterm',
        'pais_origen',
        'pais_procedencia',
        'pais_destino',
        'puerto_salida',
        'puerto_llegada',
        'transportadora',
        'agencia_aduanas',
        'fecha_embarque',
        'fecha_llegada',
        'numero_declaracion_importacion',
        'valor_fob',
        'flete_internacional',
        'seguro',
        'valor_cif',
        'aranceles',
        'impuestos',
        'gastos_aduaneros',
        'costo_total_importacion',
    ];

    protected function casts(): array
    {
        return [
            'anio' => 'integer',
            'kilometraje' => 'integer',
            'precio_adquisicion' => 'decimal:2',
            'fecha_compra' => 'date',
            'fecha_embarque' => 'date',
            'fecha_llegada' => 'date',
            'valor_fob' => 'decimal:2',
            'flete_internacional' => 'decimal:2',
            'seguro' => 'decimal:2',
            'valor_cif' => 'decimal:2',
            'aranceles' => 'decimal:2',
            'impuestos' => 'decimal:2',
            'gastos_aduaneros' => 'decimal:2',
            'costo_total_importacion' => 'decimal:2',
        ];
    }

    public function vehiculo(): BelongsTo
    {
        return $this->belongsTo(Vehiculo::class);
    }
}
