<?php

namespace App\Repositories;

use App\Interfaces\ICitaRepository;
use App\Models\Cita;
use Illuminate\Database\Eloquent\Collection;

class CitaRepository implements ICitaRepository
{
    public function all(): Collection
    {
        return Cita::all();
    }

    public function findById(int $id): ?Cita
    {
        return Cita::find($id);
    }

    public function findByUser(int $userId): Collection
    {
        return Cita::where('user_id', $userId)->get();
    }

    public function create(array $data): Cita
    {
        return Cita::create($data);
    }

    public function update(Cita $cita, array $data): bool
    {
        return $cita->update($data);
    }

    public function delete(Cita $cita): bool
    {
        return $cita->delete();
    }

    public function findPending(): Collection
    {
        return Cita::where('estado', 'pendiente')->get();
    }
}
