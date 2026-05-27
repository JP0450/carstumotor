<?php

namespace App\Services;

use App\Interfaces\IInventarioRepository;
use App\Models\Inventario;
use Illuminate\Database\Eloquent\Collection;

class InventarioService
{
    public function __construct(
        private IInventarioRepository $inventarioRepository
    ) {}

    public function listar(): Collection
    {
        return $this->inventarioRepository->all();
    }

    public function registrarEntrada(int $vehiculoId, int $cantidad, ?string $ubicacion = null): Inventario
    {
        $existente = $this->inventarioRepository->findByVehiculo($vehiculoId);

        if ($existente) {
            $nuevaCantidad = $existente->cantidad + $cantidad;
            $this->inventarioRepository->updateStock($existente->id, $nuevaCantidad);
            $existente->refresh();

            return $existente;
        }

        return $this->inventarioRepository->create([
            'vehiculo_id' => $vehiculoId,
            'cantidad' => $cantidad,
            'ubicacion' => $ubicacion,
        ]);
    }

    public function registrarSalida(int $vehiculoId, int $cantidad): bool
    {
        $inventario = $this->inventarioRepository->findByVehiculo($vehiculoId);

        if (!$inventario || $inventario->cantidad < $cantidad) {
            return false;
        }

        return $this->inventarioRepository->updateStock(
            $inventario->id,
            $inventario->cantidad - $cantidad
        );
    }

    public function obtenerAlertasBajoStock(int $minimo = 3): Collection
    {
        return $this->inventarioRepository->all()
            ->filter(fn (Inventario $item) => $item->cantidad <= $minimo)
            ->values();
    }
}
