<?php

namespace App\Http\Controllers;

use App\Http\Requests\Pedido\StorePedidoRequest;
use App\Http\Requests\Pedido\UpdateProgresoRequest;
use App\Models\User;
use App\Services\PedidoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PedidoController extends Controller
{
    public function __construct(
        private PedidoService $pedidoService,
        private \App\Services\ReportService $reportService
    ) {}

    public function store(StorePedidoRequest $request): JsonResponse
    {
        /** @var User $user */
        $user = auth()->user();

        $pedido = $this->pedidoService->crearPedido($user, $request->validated());

        return response()->json($pedido, 201);
    }

    public function index(): JsonResponse
    {
        /** @var User $user */
        $user = auth()->user();

        $pedidos = $this->pedidoService->listarPorUsuario($user->id);

        return response()->json($pedidos);
    }

    public function updateProgress(UpdateProgresoRequest $request, int $id): JsonResponse
    {
        $resultado = $this->pedidoService->actualizarProgreso($id, $request->input('progreso'));

        if (!$resultado) {
            return response()->json(['message' => 'No se pudo actualizar el progreso.'], 400);
        }

        return response()->json(['message' => 'Progreso actualizado.']);
    }

    public function uploadArchivo(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'archivo' => ['required', 'file', 'max:10240'],
        ]);

        $archivo = $request->file('archivo');
        $ruta = $archivo->store('pedidos/' . $id);

        $this->pedidoService->subirArchivo($id, [
            'nombre_original' => $archivo->getClientOriginalName(),
            'ruta' => $ruta,
            'tipo' => $archivo->getClientMimeType(),
        ]);

        return response()->json(['message' => 'Archivo subido correctamente.']);
    }

    public function archivos(int $id): JsonResponse
    {
        $archivos = $this->pedidoService->obtenerArchivos($id);

        return response()->json($archivos);
    }

    public function descargarPedidoPDF(int $id)
    {
        $pedido = \App\Models\PedidoInternacional::with('user', 'archivos')->findOrFail($id);

        // Normalizar items para la plantilla si no existen
        $items = [];
        if (property_exists($pedido, 'items') && is_array($pedido->items)) {
            $items = $pedido->items;
        }

        // Pasamos el modelo entero para la vista
        return $this->reportService->generatePdf('pdfs.pedido', [
            'pedido' => $pedido,
            'items' => $items,
        ], "pedido-{$pedido->id}.pdf");
    }
}
