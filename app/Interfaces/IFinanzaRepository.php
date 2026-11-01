<?php

namespace App\Interfaces;

use App\Models\InformeFinanciero;
use App\Models\TransaccionFinanciera;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface IFinanzaRepository
{
    public function allInformes(): Collection;

    public function findInformeById(int $id): ?InformeFinanciero;

    public function createInforme(array $data): InformeFinanciero;

    public function deleteInforme(InformeFinanciero $informe): bool;

    public function allTransacciones(): Collection;

    public function findTransaccionById(int $id): ?TransaccionFinanciera;

    public function createTransaccion(array $data): TransaccionFinanciera;

    public function paginateTransacciones(array $filters = []): LengthAwarePaginator;

    public function countTransaccionesByType(): array;

    public function countTransaccionesByTypeAndDate(string $start, string $end): Collection;

    public function updateTransaccion(TransaccionFinanciera $transaccion, array $data): TransaccionFinanciera;

    public function deleteTransaccion(TransaccionFinanciera $transaccion): bool;

    public function findTransaccionesByTipo(string $tipo): Collection;

    public function findTransaccionesByPeriodo(string $start, string $end): Collection;
}
