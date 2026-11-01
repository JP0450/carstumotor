<?php

namespace App\Repositories;

use App\Interfaces\IFinanzaRepository;
use App\Models\InformeFinanciero;
use App\Models\TransaccionFinanciera;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
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

    public function paginateTransacciones(array $filters = []): LengthAwarePaginator
    {
        return TransaccionFinanciera::query()
            ->when($filters['tipo'] ?? null, fn ($query, $tipo) => $query->where('tipo', $tipo))
            ->when($filters['fecha_inicio'] ?? null, fn ($query, $fecha) => $query->whereDate('fecha', '>=', $fecha))
            ->when($filters['fecha_fin'] ?? null, fn ($query, $fecha) => $query->whereDate('fecha', '<=', $fecha))
            ->with(['user:id,name', 'vehiculo:id,marca,modelo'])
            ->orderByDesc('fecha')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();
    }

    public function countTransaccionesByType(): array
    {
        return TransaccionFinanciera::query()
            ->selectRaw('tipo, COUNT(*) AS cantidad')
            ->groupBy('tipo')
            ->pluck('cantidad', 'tipo')
            ->all();
    }

    public function countTransaccionesByTypeAndDate(string $start, string $end): Collection
    {
        return TransaccionFinanciera::query()
            ->whereDate('fecha', '>=', $start)
            ->whereDate('fecha', '<=', $end)
            ->select('tipo', 'fecha')
            ->selectRaw('COUNT(*) AS cantidad')
            ->groupBy('tipo', 'fecha')
            ->orderBy('fecha')
            ->get();
    }

    public function updateTransaccion(TransaccionFinanciera $transaccion, array $data): TransaccionFinanciera
    {
        $transaccion->update($data);

        return $transaccion->refresh();
    }

    public function deleteTransaccion(TransaccionFinanciera $transaccion): bool
    {
        return (bool) $transaccion->delete();
    }

    public function findTransaccionesByTipo(string $tipo): Collection
    {
        return TransaccionFinanciera::where('tipo', $tipo)->get();
    }

    public function findTransaccionesByPeriodo(string $start, string $end): Collection
    {
        return TransaccionFinanciera::query()
            ->whereDate('fecha', '>=', $start)
            ->whereDate('fecha', '<=', $end)
            ->get();
    }
}
