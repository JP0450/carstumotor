<?php

namespace App\Services;

use App\Interfaces\ICitaRepository;
use App\Models\Cita;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class CitaService
{
    public function __construct(
        private ICitaRepository $citaRepository
    ) {}

    public function agendar(User $user, array $data): Cita
    {
        $data['user_id'] = $user->id;
        $data['estado'] = 'pendiente';

        return $this->citaRepository->create($data);
    }

    public function listarPorUsuario(int $userId): Collection
    {
        return $this->citaRepository->findByUser($userId);
    }

    public function cancelar(Cita $cita): bool
    {
        return $this->citaRepository->update($cita, ['estado' => 'cancelada']);
    }

    public function confirmar(Cita $cita): bool
    {
        return $this->citaRepository->update($cita, ['estado' => 'confirmada']);
    }

    public function listarPendientes(): Collection
    {
        return $this->citaRepository->findPending();
    }
}
