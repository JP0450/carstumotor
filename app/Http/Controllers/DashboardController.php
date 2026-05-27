<?php

namespace App\Http\Controllers;

use App\Services\DashboardEjecutivoService;
use App\Services\FinanzaService;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    public function __construct(
        private DashboardEjecutivoService $dashboardService,
        private FinanzaService $finanzaService
    ) {}

    public function ejecutivo(): JsonResponse
    {
        $kpis = $this->dashboardService->obtenerKPIs();

        return response()->json($kpis);
    }

    // Vista para el dashboard del contador — usa métricas financieras.
    public function contador()
    {
        $inicio = now()->startOfMonth()->toDateString();
        $fin = now()->endOfMonth()->toDateString();

        $flujo = $this->finanzaService->obtenerFlujoCaja($inicio, $fin);
        $estado = $this->finanzaService->obtenerEstadoResultados($inicio, $fin);
        $cxp = $this->finanzaService->obtenerCuentasPorPagar();
        $cxc = $this->finanzaService->obtenerCuentasPorCobrar();
        $informes = $this->finanzaService->listarInformes();

        return view('admin.dashboard.contador', compact('flujo', 'estado', 'cxp', 'cxc', 'informes'));
    }
}
