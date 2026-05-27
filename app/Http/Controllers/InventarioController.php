<?php

namespace App\Http\Controllers;

use App\Http\Requests\Inventario\UpdateStockRequest;
use App\Interfaces\IInventarioRepository;
use App\Services\InventarioService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InventarioController extends Controller
{
    public function __construct(
        private InventarioService $inventarioService,
        private IInventarioRepository $inventarioRepository
    ) {}

    public function index(): JsonResponse
    {
        return response()->json($this->inventarioService->listar());
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'vehiculo_id' => ['required', 'exists:vehiculos,id'],
            'cantidad' => ['required', 'integer', 'min:1'],
            'ubicacion' => ['nullable', 'string', 'max:100'],
        ]);

        $inventario = $this->inventarioService->registrarEntrada(
            $validated['vehiculo_id'],
            $validated['cantidad'],
            $validated['ubicacion'] ?? null
        );

        return response()->json($inventario, 201);
    }

    public function updateStock(UpdateStockRequest $request, int $id): JsonResponse
    {
        $inventario = $this->inventarioRepository->findById($id);

        if (!$inventario) {
            return response()->json(['message' => 'Inventario no encontrado.'], 404);
        }

        $this->inventarioRepository->update($inventario, $request->validated());

        return response()->json($inventario->fresh());
    }

    public function salida(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'vehiculo_id' => ['required', 'exists:vehiculos,id'],
            'cantidad' => ['required', 'integer', 'min:1'],
        ]);

        $resultado = $this->inventarioService->registrarSalida(
            $validated['vehiculo_id'],
            $validated['cantidad']
        );

        if (!$resultado) {
            return response()->json([
                'message' => 'Stock insuficiente o vehículo no encontrado en inventario.',
            ], 400);
        }

        return response()->json(['message' => 'Salida registrada correctamente.']);
    }

    public function alertas(): JsonResponse
    {
        $alertas = $this->inventarioService->obtenerAlertasBajoStock();

        return response()->json($alertas);
    }
}
