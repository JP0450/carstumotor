<?php

namespace App\Interfaces;

use App\Models\Inventario;
use Illuminate\Database\Eloquent\Collection;

interface IInventarioRepository
{
    public function all(): Collection;

    public function findById(int $id): ?Inventario;

    public function findByVehiculo(int $vehiculoId): ?Inventario;

    public function create(array $data): Inventario;

    public function update(Inventario $inventario, array $data): bool;

    public function delete(Inventario $inventario): bool;

    public function updateStock(int $inventarioId, int $cantidad): bool;
}
