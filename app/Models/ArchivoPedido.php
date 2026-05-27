<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ArchivoPedido extends Model
{
    use HasFactory;

    protected $table = 'archivos_pedidos';

    protected $fillable = [
        'pedido_internacional_id',
        'nombre_original',
        'ruta',
        'tipo',
    ];

    public function pedidoInternacional(): BelongsTo
    {
        return $this->belongsTo(PedidoInternacional::class, 'pedido_internacional_id');
    }
}
