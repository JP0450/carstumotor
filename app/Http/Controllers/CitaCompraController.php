<?php

namespace App\Http\Controllers;

use App\Http\Requests\Cita\StoreCompraCitaRequest;
use App\Models\Cita;
use App\Models\User;
use App\Models\Vehiculo;
use App\Services\CitaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class CitaCompraController extends Controller
{
    public function __construct(private CitaService $citaService) {}

    public function create(Vehiculo $vehiculo): View
    {
        $this->ensureAvailable($vehiculo);

        return view('compras.create', [
            'vehiculo' => $vehiculo,
            'user' => request()->user(),
        ]);
    }

    public function store(StoreCompraCitaRequest $request, Vehiculo $vehiculo): RedirectResponse
    {
        $this->citaService->agendarCompra(
            $request->user(),
            $vehiculo,
            $request->validated()
        );

        return redirect()->route('compras.mine')
            ->with('success', 'Tu solicitud de asesoría fue enviada a Contabilidad. El vehículo no se considera vendido hasta completar la operación.');
    }

    public function mine(Request $request): View
    {
        $solicitudes = Cita::query()
            ->with(['vehiculo', 'responsable'])
            ->where('user_id', $request->user()->id)
            ->where('tipo', 'compra')
            ->latest('fecha_hora')
            ->get();

        return view('compras.mine', compact('solicitudes'));
    }

    public function index(Request $request): View
    {
        $query = Cita::query()
            ->with(['user', 'vehiculo', 'responsable'])
            ->where('tipo', 'compra')
            ->latest('fecha_hora');

        if ($request->user()->hasRole(User::ROLE_CONTADOR)) {
            $query->where('responsable_id', $request->user()->id);
        }

        $solicitudes = $query->get();

        return view('admin.compras.index', compact('solicitudes'));
    }

    public function updateStatus(Request $request, Cita $cita): RedirectResponse
    {
        abort_unless($cita->tipo === 'compra', Response::HTTP_NOT_FOUND);
        abort_if(
            $request->user()->hasRole(User::ROLE_CONTADOR) && $cita->responsable_id !== $request->user()->id,
            Response::HTTP_NOT_FOUND
        );

        $validated = $request->validate([
            'estado' => ['required', Rule::in(['pendiente', 'confirmada', 'cancelada'])],
        ]);

        $cita->update($validated);

        return redirect()->route('admin.compras.index')
            ->with('success', 'El estado de la solicitud se actualizó. Esto no registra la venta del vehículo.');
    }

    private function ensureAvailable(Vehiculo $vehiculo): void
    {
        abort_unless(
            $vehiculo->disponible && ! $vehiculo->ventaRegistrada()->exists(),
            Response::HTTP_NOT_FOUND
        );
    }
}
