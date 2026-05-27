<?php

namespace App\Http\Controllers;

use App\Http\Requests\Cita\StoreCitaRequest;
use App\Models\User;
use App\Services\CitaService;
use Illuminate\Http\JsonResponse;

class CitaController extends Controller
{
    public function __construct(
        private CitaService $citaService,
        private \App\Services\ReportService $reportService
    ) {}

    public function store(StoreCitaRequest $request): JsonResponse
    {
        /** @var User $user */
        $user = auth()->user();

        $cita = $this->citaService->agendar($user, $request->validated());

        return response()->json($cita, 201);
    }

    public function index(): JsonResponse
    {
        /** @var User $user */
        $user = auth()->user();

        $citas = $this->citaService->listarPorUsuario($user->id);

        return response()->json($citas);
    }

    public function cancelar(int $id): JsonResponse
    {
        $cita = $this->citaService->listarPorUsuario(auth()->id())
            ->firstWhere('id', $id);

        if (!$cita) {
            return response()->json(['message' => 'Cita no encontrada.'], 404);
        }

        $this->citaService->cancelar($cita);

        return response()->json(['message' => 'Cita cancelada.']);
    }

    public function pendientes(): JsonResponse
    {
        $citas = $this->citaService->listarPendientes();

        return response()->json($citas);
    }

    public function descargarComprobantePDF(int $id)
    {
        $cita = \App\Models\Cita::with('user', 'vehiculo')->findOrFail($id);

        return $this->reportService->generatePdf('pdfs.cita', [
            'cita' => $cita,
        ], "cita-{$cita->id}.pdf");
    }
}
