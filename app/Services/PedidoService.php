<?php

namespace App\Services;

use App\Interfaces\IArchivoPedidoRepository;
use App\Interfaces\IPedidoRepository;
use App\Models\PedidoInternacional;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class PedidoService
{
    public function __construct(
        private IPedidoRepository $pedidoRepository,
        private IArchivoPedidoRepository $archivoRepository
    ) {}

    public function crearPedido(User $user, array $data): PedidoInternacional
    {
        $data['user_id'] = $user->id;
        $data['codigo'] = 'PED-' . strtoupper(uniqid());
        $data['estado'] = 'en_espera';
        $data['progreso'] = 0;

        return $this->pedidoRepository->create($data);
    }

    public function listarPorUsuario(int $userId): Collection
    {
        return $this->pedidoRepository->findByUser($userId);
    }

    public function actualizarProgreso(int $pedidoId, int $progreso): bool
    {
        if ($progreso < 0 || $progreso > 100) {
            return false;
        }

        $pedido = $this->pedidoRepository->findById($pedidoId);

        if (!$pedido) {
            return false;
        }

        $estado = match (true) {
            $progreso === 0 => 'en_espera',
            $progreso === 100 => 'entregado',
            default => 'en_transito',
        };

        return $this->pedidoRepository->update($pedido, [
            'progreso' => $progreso,
            'estado' => $estado,
        ]);
    }

    public function subirArchivo(int $pedidoId, array $archivoData): void
    {
        $archivoData['pedido_internacional_id'] = $pedidoId;
        $this->archivoRepository->create($archivoData);
    }

    public function obtenerArchivos(int $pedidoId): Collection
    {
        return $this->archivoRepository->findByPedido($pedidoId);
    }
}
