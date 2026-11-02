<?php

namespace App\Http\Controllers;

use App\Interfaces\IVehiculoRepository;
use App\Services\CatalogoService;
use App\Services\FinanzasVehiculosService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class VehiculoController extends Controller
{
    public function __construct(
        private CatalogoService $catalogoService,
        private IVehiculoRepository $vehiculoRepository,
        private FinanzasVehiculosService $finanzasVehiculos
    ) {}

    public function catalogo(Request $request): View
    {
        $filters = $request->only(['marca', 'color', 'precio_min', 'precio_max']);

        if (!empty(array_filter($filters))) {
            $vehiculos = $this->catalogoService->buscar($filters);
        } else {
            $vehiculos = $this->catalogoService->listarDisponibles();
        }

        return view('catalogo', compact('vehiculos'));
    }

    public function ficha(int $id): View
    {
        $vehiculo = $this->catalogoService->obtenerDetalle($id);

        abort_unless($vehiculo && $vehiculo->disponible, Response::HTTP_NOT_FOUND);

        return view('catalogo.show', compact('vehiculo'));
    }

    public function index(): JsonResponse
    {
        $vehiculos = $this->catalogoService->listarDisponibles();

        return response()->json($vehiculos);
    }

    public function show(int $id): JsonResponse
    {
        $vehiculo = $this->catalogoService->obtenerDetalle($id);

        if (!$vehiculo) {
            return response()->json(['message' => 'Vehículo no encontrado o no disponible.'], 404);
        }

        return response()->json($vehiculo);
    }

    public function search(Request $request): JsonResponse
    {
        $vehiculos = $this->catalogoService->buscar($request->only([
            'marca', 'color', 'precio_min', 'precio_max', 'disponible',
        ]));

        return response()->json($vehiculos);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'marca' => ['required', 'string', 'max:50'],
            'modelo' => ['required', 'string', 'max:50'],
            'color' => ['required', 'string', 'max:30'],
            'color_secundario' => ['nullable', 'string', 'max:30'],
            'puertas' => ['required', 'integer', 'min:1', 'max:5'],
            'hp' => ['required', 'integer', 'min:1', 'max:100000'],
            'precio_cliente' => ['required', 'numeric', 'min:0'],
            'costo_empresa' => ['required', 'numeric', 'gt:0'],
            'imagen' => ['nullable', 'string', 'max:255'],
            'disponible' => ['boolean'],
        ]);

        $vehiculo = DB::transaction(function () use ($validated, $request) {
            $vehiculo = $this->vehiculoRepository->create($validated);
            $this->finanzasVehiculos->registrarCompra($vehiculo, $request->user()->id);

            return $vehiculo;
        });

        return response()->json($vehiculo, 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $vehiculo = $this->vehiculoRepository->findById($id);

        if (!$vehiculo) {
            return response()->json(['message' => 'Vehículo no encontrado.'], 404);
        }

        $validated = $request->validate([
            'marca' => ['sometimes', 'string', 'max:50'],
            'modelo' => ['sometimes', 'string', 'max:50'],
            'color' => ['sometimes', 'string', 'max:30'],
            'color_secundario' => ['sometimes', 'nullable', 'string', 'max:30'],
            'puertas' => ['sometimes', 'integer', 'min:1', 'max:5'],
            'hp' => ['sometimes', 'integer', 'min:1', 'max:100000'],
            'precio_cliente' => ['sometimes', 'numeric', 'min:0'],
            'costo_empresa' => ['sometimes', 'numeric', 'min:0'],
            'imagen' => ['sometimes', 'nullable', 'string', 'max:255'],
            'disponible' => ['sometimes', 'boolean'],
        ]);

        if ($vehiculo->ventaRegistrada()->exists()) {
            $validated['disponible'] = false;
        }

        $this->vehiculoRepository->update($vehiculo, $validated);

        return response()->json($vehiculo->fresh());
    }

    public function destroy(int $id): JsonResponse
    {
        $vehiculo = $this->vehiculoRepository->findById($id);

        if (!$vehiculo) {
            return response()->json(['message' => 'Vehículo no encontrado.'], 404);
        }

        $this->vehiculoRepository->delete($vehiculo);

        return response()->json(['message' => 'Vehículo eliminado.']);
    }
}
