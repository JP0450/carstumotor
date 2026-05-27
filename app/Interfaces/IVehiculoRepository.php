<?php

namespace App\Interfaces;

use App\Models\Vehiculo;
use Illuminate\Database\Eloquent\Collection;

interface IVehiculoRepository
{
    public function all(): Collection;

    public function findById(int $id): ?Vehiculo;

    public function findAvailable(): Collection;

    public function create(array $data): Vehiculo;

    public function update(Vehiculo $vehiculo, array $data): bool;

    public function delete(Vehiculo $vehiculo): bool;

    public function search(array $filters): Collection;
}
