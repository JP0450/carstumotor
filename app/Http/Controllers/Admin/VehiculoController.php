<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Interfaces\IVehiculoRepository;
use App\Models\Vehiculo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class VehiculoController extends Controller
{
    public function __construct(
        private IVehiculoRepository $vehiculoRepository
    ) {}

    public function index(): View
    {
        $vehiculos = $this->vehiculoRepository->all()->sortByDesc('id')->values();

        return view('admin.vehiculos.index', compact('vehiculos'));
    }

    public function create(): View
    {
        return view('admin.vehiculos.create', $this->formOptions());
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'marca' => ['required', 'string', 'max:50'],
            'modelo' => ['required', 'string', 'max:50'],
            'color' => ['required', 'string', 'max:30'],
            'color_secundario' => ['nullable', 'string', 'max:30'],
            'puertas' => ['required', 'integer', 'min:1', 'max:5'],
            'hp' => ['required', 'integer', 'min:1', 'max:100000'],
            'precio_cliente' => ['required', 'numeric', 'min:0'],
            'costo_empresa' => ['required', 'numeric', 'min:0'],
            'imagen' => ['nullable', 'string', 'max:255'],
        ]);

        $validated['disponible'] = $request->boolean('disponible');

        $this->vehiculoRepository->create($validated);

        return redirect()->route('admin.vehiculos.index')
            ->with('success', 'Vehículo creado correctamente.');
    }

    public function edit(Vehiculo $vehiculo): View
    {
        return view('admin.vehiculos.edit', array_merge(
            compact('vehiculo'),
            $this->formOptions()
        ));
    }

    public function update(Request $request, Vehiculo $vehiculo): RedirectResponse
    {
        $validated = $request->validate([
            'marca' => ['required', 'string', 'max:50'],
            'modelo' => ['required', 'string', 'max:50'],
            'color' => ['required', 'string', 'max:30'],
            'color_secundario' => ['nullable', 'string', 'max:30'],
            'puertas' => ['required', 'integer', 'min:1', 'max:5'],
            'hp' => ['required', 'integer', 'min:1', 'max:100000'],
            'precio_cliente' => ['required', 'numeric', 'min:0'],
            'costo_empresa' => ['required', 'numeric', 'min:0'],
            'imagen' => ['nullable', 'string', 'max:255'],
        ]);

        $validated['disponible'] = $request->boolean('disponible');

        $this->vehiculoRepository->update($vehiculo, $validated);

        return redirect()->route('admin.vehiculos.index')
            ->with('success', 'Vehículo actualizado correctamente.');
    }

    public function destroy(Vehiculo $vehiculo): RedirectResponse
    {
        $disk = Storage::disk('local');
        foreach ($vehiculo->documentos()->get(['ruta']) as $documento) {
            if ($disk->exists($documento->ruta) && ! $disk->delete($documento->ruta)) {
                throw new \RuntimeException('No se pudo eliminar un documento asociado al vehículo.');
            }
        }

        $this->vehiculoRepository->delete($vehiculo);

        return redirect()->route('admin.vehiculos.index')
            ->with('success', 'Vehículo eliminado del catálogo.');
    }

    private function formOptions(): array
    {
        $vehiculos = Vehiculo::query()->get(['marca', 'modelo', 'color', 'color_secundario']);

        return [
            'marcas' => $vehiculos->pluck('marca')->filter()->unique()->sort()->values(),
            'modelos' => $vehiculos->pluck('modelo')->filter()->unique()->sort()->values(),
            'modelosPorMarca' => $vehiculos
                ->groupBy(fn (Vehiculo $vehiculo) => mb_strtolower(trim($vehiculo->marca)))
                ->map(fn ($vehiculosDeMarca) => $vehiculosDeMarca->pluck('modelo')->filter()->unique()->values()),
            'colores' => $vehiculos->flatMap(fn (Vehiculo $vehiculo) => [
                $vehiculo->color,
                $vehiculo->color_secundario,
            ])->filter()->unique()->sort()->values(),
        ];
    }
}
