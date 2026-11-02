<?php

namespace App\Services;

use App\Interfaces\ICitaRepository;
use App\Models\Cita;
use App\Models\User;
use App\Models\Vehiculo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

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

    public function agendarCompra(User $user, Vehiculo $vehiculo, array $data): Cita
    {
        return DB::transaction(function () use ($user, $vehiculo, $data): Cita {
            $vehiculoDisponible = Vehiculo::query()
                ->whereKey($vehiculo->id)
                ->where('disponible', true)
                ->whereDoesntHave('ventaRegistrada')
                ->lockForUpdate()
                ->first();

            if (! $vehiculoDisponible) {
                throw ValidationException::withMessages([
                    'vehiculo_id' => 'Este vehículo ya no está disponible. Actualiza el catálogo para ver otras opciones.',
                ]);
            }

            $contador = User::query()
                ->where('role', User::ROLE_CONTADOR)
                ->withCount([
                    'citasAsignadas as solicitudes_pendientes_count' => fn (Builder $query) => $query
                        ->where('tipo', 'compra')
                        ->where('estado', 'pendiente'),
                ])
                ->orderBy('solicitudes_pendientes_count')
                ->orderBy('id')
                ->first();

            if (! $contador) {
                throw ValidationException::withMessages([
                    'contacto' => 'En este momento no hay un contador disponible para recibir la solicitud. Intenta más tarde.',
                ]);
            }

            return Cita::query()->create([
                'user_id' => $user->id,
                'responsable_id' => $contador->id,
                'vehiculo_id' => $vehiculoDisponible->id,
                'tipo' => 'compra',
                'nombre_contacto' => $data['nombre_contacto'],
                'telefono_contacto' => $data['telefono_contacto'],
                'fecha_hora' => $data['fecha_hora'],
                'motivo' => $data['motivo'] ?? 'Asesoría para compra de vehículo',
                'estado' => 'pendiente',
            ]);
        });
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
