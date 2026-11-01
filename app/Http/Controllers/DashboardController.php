<?php

namespace App\Http\Controllers;

use App\Services\DashboardEjecutivoService;
use App\Services\DashboardGerencialService;
use App\Services\ReportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DashboardController extends Controller
{
    public function __construct(
        private DashboardEjecutivoService $dashboardService,
        private DashboardGerencialService $gerencialService,
        private ReportService $reportService
    ) {}

    public function ejecutivo(): JsonResponse
    {
        $kpis = $this->dashboardService->obtenerKPIs();

        return response()->json($kpis);
    }

    public function gerencial(Request $request)
    {
        $filtros = $this->validarFiltros($request);
        $periodo = $filtros['periodo'] ?? 'mes_actual';
        $dashboard = $this->gerencialService->obtenerDashboard(
            $periodo,
            $filtros['fecha_inicio'] ?? null,
            $filtros['fecha_fin'] ?? null,
            $filtros['moneda'] ?? 'COP'
        );

        return view('admin.dashboard.gerencial', [
            'dashboard' => $dashboard,
            'periodos' => $this->gerencialService->obtenerOpcionesPeriodo(),
        ]);
    }

    public function exportarPdf(Request $request)
    {
        $dashboard = $this->obtenerDatosGerenciales($request);

        return $this->reportService->generatePdf(
            'admin.dashboard.gerencial-pdf',
            compact('dashboard'),
            'informe-gerencial.pdf'
        );
    }

    public function exportarCsv(Request $request)
    {
        $dashboard = $this->obtenerDatosGerenciales($request);

        return response()->streamDownload(function () use ($dashboard): void {
            $output = fopen('php://output', 'wb');
            fwrite($output, "\xEF\xBB\xBF");

            fputcsv($output, ['Informe gerencial', 'Valor', 'Observación'], ';', '"', '\\');
            fputcsv($output, ['Periodo', $dashboard['periodo']['etiqueta'], $dashboard['periodo']['inicio'].' a '.$dashboard['periodo']['fin']], ';', '"', '\\');

            foreach ($dashboard['kpis'] as $clave => $kpi) {
                $valor = $kpi['valor'] === null ? 'Sin datos suficientes' : $kpi['valor'];
                fputcsv($output, [$clave, $valor, $kpi['nota']], ';', '"', '\\');
            }

            fputcsv($output, [], ';', '"', '\\');
            fputcsv($output, ['Movimientos mensuales', 'Ingresos '.$dashboard['periodo']['moneda'], 'Egresos '.$dashboard['periodo']['moneda']], ';', '"', '\\');
            foreach ($dashboard['finanzas']['serie']['labels'] as $indice => $mes) {
                fputcsv($output, [
                    $mes,
                    $dashboard['finanzas']['serie']['ingresos'][$indice],
                    $dashboard['finanzas']['serie']['egresos'][$indice],
                ], ';', '"', '\\');
            }

            fputcsv($output, [], ';', '"', '\\');
            fputcsv($output, ['Ventas por mes', 'Vehículos vendidos'], ';', '"', '\\');
            foreach ($dashboard['finanzas']['ventas']['labels'] as $indice => $mes) {
                fputcsv($output, [$mes, $dashboard['finanzas']['ventas']['cantidades'][$indice]], ';', '"', '\\');
            }

            fclose($output);
        }, 'informe-gerencial.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    private function validarFiltros(Request $request): array
    {
        return $request->validate([
            'periodo' => ['nullable', 'string', Rule::in(array_keys($this->gerencialService->obtenerOpcionesPeriodo()))],
            'moneda' => ['nullable', 'string', Rule::in(['COP', 'USD', 'EUR'])],
            'fecha_inicio' => ['nullable', 'required_if:periodo,personalizado', 'date'],
            'fecha_fin' => ['nullable', 'required_if:periodo,personalizado', 'date', 'after_or_equal:fecha_inicio'],
        ]);
    }

    private function obtenerDatosGerenciales(Request $request): array
    {
        $filtros = $this->validarFiltros($request);

        return $this->gerencialService->obtenerDashboard(
            $filtros['periodo'] ?? 'mes_actual',
            $filtros['fecha_inicio'] ?? null,
            $filtros['fecha_fin'] ?? null,
            $filtros['moneda'] ?? 'COP'
        );
    }
}
