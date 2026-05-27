<?php

namespace App\Interfaces;

use App\Models\PedidoInternacional;
use Illuminate\Database\Eloquent\Collection;

interface IPedidoRepository
{
    public function all(): Collection;

    public function findById(int $id): ?PedidoInternacional;

    public function findByUser(int $userId): Collection;

    public function create(array $data): PedidoInternacional;

    public function update(PedidoInternacional $pedido, array $data): bool;

    public function delete(PedidoInternacional $pedido): bool;

    public function updateProgress(PedidoInternacional $pedido, int $progress): bool;

    public function findByStatus(string $status): Collection;
}
