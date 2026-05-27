<?php

namespace App\Interfaces;

use App\Models\Cita;
use Illuminate\Database\Eloquent\Collection;

interface ICitaRepository
{
    public function all(): Collection;

    public function findById(int $id): ?Cita;

    public function findByUser(int $userId): Collection;

    public function create(array $data): Cita;

    public function update(Cita $cita, array $data): bool;

    public function delete(Cita $cita): bool;

    public function findPending(): Collection;
}
