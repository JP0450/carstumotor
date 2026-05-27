<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Interfaces\IVehiculoRepository;
use App\Models\Vehiculo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
        return view('admin.vehiculos.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'marca' => ['required', 'string', 'max:50'],
            'modelo' => ['required', 'string', 'max:50'],
            'color' => ['required', 'string', 'max:30'],
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
        return view('admin.vehiculos.edit', compact('vehiculo'));
    }

    public function update(Request $request, Vehiculo $vehiculo): RedirectResponse
    {
        $validated = $request->validate([
            'marca' => ['required', 'string', 'max:50'],
            'modelo' => ['required', 'string', 'max:50'],
            'color' => ['required', 'string', 'max:30'],
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
        $this->vehiculoRepository->delete($vehiculo);

        return redirect()->route('admin.vehiculos.index')
            ->with('success', 'Vehículo eliminado del catálogo.');
    }
}
