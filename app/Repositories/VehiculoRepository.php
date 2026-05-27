<?php

namespace App\Repositories;

use App\Interfaces\IVehiculoRepository;
use App\Models\Vehiculo;
use Illuminate\Database\Eloquent\Collection;

class VehiculoRepository implements IVehiculoRepository
{
    public function all(): Collection
    {
        return Vehiculo::all();
    }

    public function findById(int $id): ?Vehiculo
    {
        return Vehiculo::find($id);
    }

    public function findAvailable(): Collection
    {
        return Vehiculo::where('disponible', true)->get();
    }

    public function create(array $data): Vehiculo
    {
        return Vehiculo::create($data);
    }

    public function update(Vehiculo $vehiculo, array $data): bool
    {
        return $vehiculo->update($data);
    }

    public function delete(Vehiculo $vehiculo): bool
    {
        return $vehiculo->delete();
    }

    public function search(array $filters): Collection
    {
        $query = Vehiculo::query();

        if (isset($filters['marca'])) {
            $query->where('marca', 'like', '%' . $filters['marca'] . '%');
        }

        if (isset($filters['color'])) {
            $query->where('color', $filters['color']);
        }

        if (isset($filters['precio_min'])) {
            $query->where('precio_cliente', '>=', $filters['precio_min']);
        }

        if (isset($filters['precio_max'])) {
            $query->where('precio_cliente', '<=', $filters['precio_max']);
        }

        if (isset($filters['disponible'])) {
            $query->where('disponible', $filters['disponible']);
        }

        return $query->get();
    }
}
