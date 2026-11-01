<?php

namespace App\Http\Controllers;

use App\Http\Requests\Finanza\StoreTransaccionRequest;
use App\Models\TransaccionFinanciera;
use App\Services\FinanzaService;
use App\Services\ReportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class FinanzaController extends Controller
{
    public function __construct(
        private FinanzaService $finanzaService,
        private ReportService $reportService
    ) {}

    public function storeTransaccion(StoreTransaccionRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['user_id'] = auth()->id();

        $transaccion = $this->finanzaService->registrarTransaccion($data);

        return response()->json($transaccion, 201);
    }

    public function listarTransacciones(Request $request): JsonResponse
    {
        $filtros = $request->validate([
            'tipo' => ['nullable', 'string', 'in:ingreso,gasto,impuesto,costo_venta,cuenta_por_pagar,cuenta_por_cobrar'],
            'fecha_inicio' => ['nullable', 'date'],
            'fecha_fin' => ['nullable', 'date', 'after_or_equal:fecha_inicio'],
        ]);

        return response()->json($this->finanzaService->listarMovimientos($filtros));
    }

    public function updateTransaccion(StoreTransaccionRequest $request, TransaccionFinanciera $transaccion): JsonResponse
    {
        $transaccion = $this->finanzaService->actualizarTransaccion($transaccion, $request->validated());

        return response()->json($transaccion);
    }

    public function destroyTransaccion(TransaccionFinanciera $transaccion): JsonResponse
    {
        $this->finanzaService->eliminarTransaccion($transaccion);

        return response()->json(null, 204);
    }

    public function listarInformes(): JsonResponse
    {
        $informes = $this->finanzaService->listarInformes();

        return response()->json($informes);
    }

    public function flujoCaja(Request $request): JsonResponse
    {
        $request->validate([
            'inicio' => ['required', 'date'],
            'fin' => ['required', 'date', 'after_or_equal:inicio'],
            'moneda' => ['nullable', Rule::in(['COP', 'USD', 'EUR'])],
        ]);

        $flujo = $this->finanzaService->obtenerFlujoCaja(
            $request->input('inicio'),
            $request->input('fin'),
            $request->input('moneda')
        );

        return response()->json($flujo);
    }

    public function estadoResultados(Request $request): JsonResponse
    {
        $request->validate([
            'inicio' => ['required', 'date'],
            'fin' => ['required', 'date', 'after_or_equal:inicio'],
            'moneda' => ['nullable', Rule::in(['COP', 'USD', 'EUR'])],
        ]);

        $estado = $this->finanzaService->obtenerEstadoResultados(
            $request->input('inicio'),
            $request->input('fin'),
            $request->input('moneda')
        );

        return response()->json($estado);
    }

    public function cuentasPorPagar(): JsonResponse
    {
        return response()->json($this->finanzaService->obtenerCuentasPorPagar());
    }

    public function cuentasPorCobrar(): JsonResponse
    {
        return response()->json($this->finanzaService->obtenerCuentasPorCobrar());
    }

    public function subirInforme(Request $request): JsonResponse
    {
        $request->validate([
            'tipo' => ['required', 'string', 'max:50'],
            'periodo' => ['required', 'string', 'max:20'],
            'archivo' => ['required', 'file', 'max:10240'],
        ]);

        $archivo = $request->file('archivo');
        $ruta = $archivo->store('informes');

        $informe = $this->finanzaService->subirInforme([
            'user_id' => auth()->id(),
            'tipo' => $request->input('tipo'),
            'periodo' => $request->input('periodo'),
            'nombre_archivo' => $archivo->getClientOriginalName(),
            'ruta' => $ruta,
        ]);

        return response()->json($informe, 201);
    }

    public function descargarReportePDF(Request $request)
    {
        $request->validate([
            'inicio' => ['required', 'date'],
            'fin' => ['required', 'date', 'after_or_equal:inicio'],
            'moneda' => ['nullable', Rule::in(['COP', 'USD', 'EUR'])],
        ]);

        $inicio = $request->input('inicio');
        $fin = $request->input('fin');

        $estado = $this->finanzaService->obtenerEstadoResultados($inicio, $fin, $request->input('moneda'));
        if ($estado['ingresos'] === null) {
            return response()->json([
                'message' => 'Selecciona una moneda para generar un informe sin mezclar divisas.',
                'por_moneda' => $estado['por_moneda'],
                'registros_sin_moneda' => $estado['registros_sin_moneda'],
            ], 422);
        }

        // Transformar a líneas simples para la plantilla PDF
        $lines = [
            ['name' => 'Ingresos', 'debit' => $estado['ingresos'] ?? 0, 'credit' => ''],
            ['name' => 'Costos de ventas', 'debit' => $estado['costos_ventas'] ?? 0, 'credit' => ''],
            ['name' => 'Gastos operativos', 'debit' => $estado['gastos_operativos'] ?? 0, 'credit' => ''],
            ['name' => 'Impuestos', 'debit' => $estado['impuestos'] ?? 0, 'credit' => ''],
            ['name' => 'Utilidad neta', 'debit' => $estado['utilidad_neta'] ?? 0, 'credit' => ''],
        ];

        $period = sprintf('%s - %s · %s', $inicio, $fin, $estado['moneda'] ?? 'sin moneda');

        return $this->reportService->generatePdf('pdfs.reporte-financiero', [
            'lines' => $lines,
            'period' => $period,
        ], 'reporte-financiero.pdf');
    }
}
