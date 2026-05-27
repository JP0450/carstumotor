<?php

namespace App\Repositories;

use App\Interfaces\IFinanzaRepository;
use App\Models\InformeFinanciero;
use App\Models\TransaccionFinanciera;
use Illuminate\Database\Eloquent\Collection;

class FinanzaRepository implements IFinanzaRepository
{
    public function allInformes(): Collection
    {
        return InformeFinanciero::all();
    }

    public function findInformeById(int $id): ?InformeFinanciero
    {
        return InformeFinanciero::find($id);
    }

    public function createInforme(array $data): InformeFinanciero
    {
        return InformeFinanciero::create($data);
    }

    public function deleteInforme(InformeFinanciero $informe): bool
    {
        return $informe->delete();
    }

    public function allTransacciones(): Collection
    {
        return TransaccionFinanciera::all();
    }

    public function findTransaccionById(int $id): ?TransaccionFinanciera
    {
        return TransaccionFinanciera::find($id);
    }

    public function createTransaccion(array $data): TransaccionFinanciera
    {
        return TransaccionFinanciera::create($data);
    }

    public function findTransaccionesByTipo(string $tipo): Collection
    {
        return TransaccionFinanciera::where('tipo', $tipo)->get();
    }

    public function findTransaccionesByPeriodo(string $start, string $end): Collection
    {
        return TransaccionFinanciera::whereBetween('fecha', [$start, $end])->get();
    }
}
