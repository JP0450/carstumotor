<?php

namespace App\Repositories;

use App\Interfaces\IArchivoPedidoRepository;
use App\Models\ArchivoPedido;
use Illuminate\Database\Eloquent\Collection;

class ArchivoPedidoRepository implements IArchivoPedidoRepository
{
    public function findByPedido(int $pedidoId): Collection
    {
        return ArchivoPedido::where('pedido_internacional_id', $pedidoId)->get();
    }

    public function create(array $data): ArchivoPedido
    {
        return ArchivoPedido::create($data);
    }

    public function delete(ArchivoPedido $archivo): bool
    {
        return $archivo->delete();
    }
}
