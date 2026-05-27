<?php

namespace App\Repositories;

use App\Interfaces\IInventarioRepository;
use App\Models\Inventario;
use Illuminate\Database\Eloquent\Collection;

class InventarioRepository implements IInventarioRepository
{
    public function all(): Collection
    {
        return Inventario::with('vehiculo')->get();
    }

    public function findById(int $id): ?Inventario
    {
        return Inventario::find($id);
    }

    public function findByVehiculo(int $vehiculoId): ?Inventario
    {
        return Inventario::where('vehiculo_id', $vehiculoId)->first();
    }

    public function create(array $data): Inventario
    {
        return Inventario::create($data);
    }

    public function update(Inventario $inventario, array $data): bool
    {
        return $inventario->update($data);
    }

    public function delete(Inventario $inventario): bool
    {
        return $inventario->delete();
    }

    public function updateStock(int $inventarioId, int $cantidad): bool
    {
        $inventario = $this->findById($inventarioId);

        if (!$inventario) {
            return false;
        }

        return $inventario->update(['cantidad' => $cantidad]);
    }
}
