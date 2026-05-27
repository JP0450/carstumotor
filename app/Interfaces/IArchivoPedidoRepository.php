<?php

namespace App\Interfaces;

use App\Models\ArchivoPedido;
use Illuminate\Database\Eloquent\Collection;

interface IArchivoPedidoRepository
{
    public function findByPedido(int $pedidoId): Collection;

    public function create(array $data): ArchivoPedido;

    public function delete(ArchivoPedido $archivo): bool;
}
