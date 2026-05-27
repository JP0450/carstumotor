<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PedidoInternacional extends Model
{
    use HasFactory;

    protected $table = 'pedidos_internacionales';

    protected $fillable = [
        'user_id',
        'codigo',
        'descripcion',
        'proveedor',
        'valor',
        'progreso',
        'estado',
        'fecha_pedido',
        'fecha_estimada_llegada',
    ];

    protected function casts(): array
    {
        return [
            'valor' => 'decimal:2',
            'progreso' => 'integer',
            'fecha_pedido' => 'date',
            'fecha_estimada_llegada' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function archivos(): HasMany
    {
        return $this->hasMany(ArchivoPedido::class, 'pedido_internacional_id');
    }
}
