<?php

namespace App\Repositories;

use App\Interfaces\IPedidoRepository;
use App\Models\PedidoInternacional;
use Illuminate\Database\Eloquent\Collection;

class PedidoRepository implements IPedidoRepository
{
    public function all(): Collection
    {
        return PedidoInternacional::all();
    }

    public function findById(int $id): ?PedidoInternacional
    {
        return PedidoInternacional::find($id);
    }

    public function findByUser(int $userId): Collection
    {
        return PedidoInternacional::where('user_id', $userId)->get();
    }

    public function create(array $data): PedidoInternacional
    {
        return PedidoInternacional::create($data);
    }

    public function update(PedidoInternacional $pedido, array $data): bool
    {
        return $pedido->update($data);
    }

    public function delete(PedidoInternacional $pedido): bool
    {
        return $pedido->delete();
    }

    public function updateProgress(PedidoInternacional $pedido, int $progress): bool
    {
        return $pedido->update(['progreso' => $progress]);
    }

    public function findByStatus(string $status): Collection
    {
        return PedidoInternacional::where('estado', $status)->get();
    }
}
