<?php

namespace App\Services;

use App\Interfaces\IVehiculoRepository;
use App\Models\Vehiculo;
use Illuminate\Database\Eloquent\Collection;

class CatalogoService
{
    public function __construct(
        private IVehiculoRepository $vehiculoRepository
    ) {}

    public function listarDisponibles(): Collection
    {
        return $this->vehiculoRepository->findAvailable()->makeHidden(['costo_empresa']);
    }

    public function obtenerDetalle(int $id): ?Vehiculo
    {
        $vehiculo = $this->vehiculoRepository->findById($id);

        if ($vehiculo) {
            $vehiculo->makeHidden(['costo_empresa']);
        }

        return $vehiculo;
    }

    public function buscar(array $filters): Collection
    {
        $resultados = $this->vehiculoRepository->search($filters);

        return $resultados->makeHidden(['costo_empresa']);
    }
}
