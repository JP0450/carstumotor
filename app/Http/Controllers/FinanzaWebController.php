<?php

namespace App\Http\Controllers;

use App\Http\Requests\Finanza\MovimientoFinancieroRequest;
use App\Models\TransaccionFinanciera;
use App\Models\Vehiculo;
use App\Services\FinanzaService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FinanzaWebController extends Controller
{
    private const TIPOS = ['ingreso', 'gasto', 'impuesto', 'costo_venta'];

    private const ETIQUETAS_TIPO = [
        'ingreso' => 'Ingreso',
        'gasto' => 'Gasto',
        'impuesto' => 'Impuesto',
        'costo_venta' => 'Costo de venta',
    ];

    public function __construct(private FinanzaService $finanzaService) {}

    public function index(Request $request): View
    {
        $filtros = $this->validarFiltros($request);

        return $this->vistaMovimientos($filtros);
    }

    public function edit(Request $request, TransaccionFinanciera $transaccion): View
    {
        $filtros = $this->validarFiltros($request);

        return $this->vistaMovimientos($filtros, $transaccion);
    }

    public function store(MovimientoFinancieroRequest $request)
    {
        $this->finanzaService->registrarTransaccion([
            ...$request->validated(),
            'user_id' => $request->user()->id,
        ]);

        return redirect()->route('finanzas.index')->with('success', 'Movimiento financiero registrado.');
    }

    public function update(MovimientoFinancieroRequest $request, TransaccionFinanciera $transaccion)
    {
        $this->finanzaService->actualizarTransaccion($transaccion, $request->validated());

        return redirect()->route('finanzas.index')->with('success', 'Movimiento financiero actualizado.');
    }

    public function destroy(TransaccionFinanciera $transaccion)
    {
        $this->finanzaService->eliminarTransaccion($transaccion);

        return redirect()->route('finanzas.index')->with('success', 'Movimiento financiero eliminado.');
    }

    private function validarFiltros(Request $request): array
    {
        return $request->validate([
            'tipo' => ['nullable', 'string', 'in:'.implode(',', self::TIPOS)],
            'fecha_inicio' => ['nullable', 'date'],
            'fecha_fin' => ['nullable', 'date', 'after_or_equal:fecha_inicio'],
        ]);
    }

    private function vistaMovimientos(array $filtros, ?TransaccionFinanciera $transaccion = null): View
    {
        return view('finanzas.index', [
            'transacciones' => $this->finanzaService->listarMovimientos($filtros),
            'conteos' => $this->finanzaService->obtenerConteosMovimientos(),
            'serieMensual' => $this->finanzaService->obtenerConteosMensualesMovimientos(),
            'vehiculos' => Vehiculo::query()->orderBy('marca')->orderBy('modelo')->get(['id', 'marca', 'modelo']),
            'filtros' => $filtros,
            'transaccion' => $transaccion,
            'tipos' => self::ETIQUETAS_TIPO,
        ]);
    }
}
