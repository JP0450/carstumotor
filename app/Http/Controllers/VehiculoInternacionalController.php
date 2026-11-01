<?php

namespace App\Http\Controllers;

use App\Models\Vehiculo;
use App\Models\VehiculoInformacionInternacional;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class VehiculoInternacionalController extends Controller
{
    private const TIPOS_DOCUMENTO = [
        'Factura comercial',
        'Bill of Lading o documento de transporte',
        'Certificado de origen',
        'Título o certificado de propiedad',
        'Declaración de importación',
        'Póliza de seguro de transporte',
        'Certificado de inspección técnica',
        'Contrato de compraventa',
    ];

    public function index(): View
    {
        $vehiculos = Vehiculo::query()
            ->with(['informacionInternacional', 'documentos'])
            ->orderByDesc('id')
            ->get();

        return view('internacional.vehiculos.index', compact('vehiculos'));
    }

    public function edit(Vehiculo $vehiculo): View
    {
        $vehiculo->load(['informacionInternacional', 'documentos']);
        $info = $vehiculo->informacionInternacional;
        $esEjemplo = $info === null;

        if ($esEjemplo) {
            $info = new VehiculoInformacionInternacional($this->datosFicticios($vehiculo));
        }

        $tiposDocumento = self::TIPOS_DOCUMENTO;

        return view('internacional.vehiculos.ficha', compact('vehiculo', 'info', 'esEjemplo', 'tiposDocumento'));
    }

    public function update(Request $request, Vehiculo $vehiculo): RedirectResponse
    {
        $validated = $request->validate([
            'anio' => ['nullable', 'integer', 'between:1886,2100'],
            'version' => ['nullable', 'string', 'max:255'],
            'vin' => ['nullable', 'string', 'max:30'],
            'numero_motor' => ['nullable', 'string', 'max:255'],
            'tipo_vehiculo' => ['nullable', 'string', 'max:255'],
            'kilometraje' => ['nullable', 'integer', 'min:0'],
            'combustible' => ['nullable', 'string', 'max:255'],
            'estado' => ['nullable', 'string', 'max:255'],
            'proveedor_internacional' => ['nullable', 'string', 'max:255'],
            'pais_compra' => ['nullable', 'string', 'max:255'],
            'precio_adquisicion' => ['nullable', 'numeric', 'min:0'],
            'moneda' => ['nullable', 'string', 'max:10'],
            'fecha_compra' => ['nullable', 'date'],
            'incoterm' => ['nullable', 'string', 'max:20'],
            'pais_origen' => ['nullable', 'string', 'max:255'],
            'pais_procedencia' => ['nullable', 'string', 'max:255'],
            'pais_destino' => ['nullable', 'string', 'max:255'],
            'puerto_salida' => ['nullable', 'string', 'max:255'],
            'puerto_llegada' => ['nullable', 'string', 'max:255'],
            'transportadora' => ['nullable', 'string', 'max:255'],
            'agencia_aduanas' => ['nullable', 'string', 'max:255'],
            'fecha_embarque' => ['nullable', 'date'],
            'fecha_llegada' => ['nullable', 'date', 'after_or_equal:fecha_embarque'],
            'numero_declaracion_importacion' => ['nullable', 'string', 'max:255'],
            'valor_fob' => ['nullable', 'numeric', 'min:0'],
            'flete_internacional' => ['nullable', 'numeric', 'min:0'],
            'seguro' => ['nullable', 'numeric', 'min:0'],
            'valor_cif' => ['nullable', 'numeric', 'min:0'],
            'aranceles' => ['nullable', 'numeric', 'min:0'],
            'impuestos' => ['nullable', 'numeric', 'min:0'],
            'gastos_aduaneros' => ['nullable', 'numeric', 'min:0'],
            'costo_total_importacion' => ['nullable', 'numeric', 'min:0'],
        ]);

        $vehiculo->informacionInternacional()->updateOrCreate(
            ['vehiculo_id' => $vehiculo->id],
            $validated
        );

        return redirect()->route('internacional.vehiculos.edit', $vehiculo)
            ->with('success', 'La ficha internacional del vehículo se guardó correctamente.');
    }

    public function storeDocument(Request $request, Vehiculo $vehiculo): RedirectResponse
    {
        $validated = $request->validate([
            'tipo' => ['required', 'string', Rule::in(self::TIPOS_DOCUMENTO)],
            'archivo' => ['required', 'file', 'mimes:pdf', 'max:10240'],
        ]);

        $archivo = $request->file('archivo');
        $ruta = $archivo->store("vehiculos/{$vehiculo->id}/documentos", 'local');

        if ($ruta === false) {
            throw new \RuntimeException('No se pudo guardar el PDF del vehículo.');
        }

        try {
            $vehiculo->documentos()->create([
                'user_id' => $request->user()->id,
                'tipo' => $validated['tipo'],
                'nombre_archivo' => $archivo->getClientOriginalName(),
                'ruta' => $ruta,
            ]);
        } catch (\Throwable $exception) {
            Storage::disk('local')->delete($ruta);

            throw $exception;
        }

        return redirect()->route('internacional.vehiculos.edit', $vehiculo)
            ->with('success', 'El documento se adjuntó correctamente.');
    }

    public function showDocument(Vehiculo $vehiculo, int $documento): BinaryFileResponse
    {
        $documento = $vehiculo->documentos()->findOrFail($documento);
        $disk = Storage::disk('local');

        abort_unless($disk->exists($documento->ruta), 404, 'El archivo solicitado no está disponible.');

        return response()->file($disk->path($documento->ruta), [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    public function downloadDocument(Vehiculo $vehiculo, int $documento): StreamedResponse
    {
        $documento = $vehiculo->documentos()->findOrFail($documento);

        abort_unless(Storage::disk('local')->exists($documento->ruta), 404, 'El archivo solicitado no está disponible.');

        return Storage::disk('local')->download($documento->ruta, $documento->nombre_archivo);
    }

    public function destroyDocument(Vehiculo $vehiculo, int $documento): RedirectResponse
    {
        $documento = $vehiculo->documentos()->findOrFail($documento);
        $disk = Storage::disk('local');

        if ($disk->exists($documento->ruta) && ! $disk->delete($documento->ruta)) {
            throw new \RuntimeException('No se pudo eliminar el archivo del almacenamiento.');
        }

        $documento->delete();

        return redirect()->route('internacional.vehiculos.edit', $vehiculo)
            ->with('success', 'El documento se eliminó correctamente.');
    }

    private function datosFicticios(Vehiculo $vehiculo): array
    {
        return [
            'anio' => 2022,
            'version' => 'Edición de demostración',
            'vin' => "DEMO-VIN-{$vehiculo->id}",
            'numero_motor' => "DEMO-MOTOR-{$vehiculo->id}",
            'tipo_vehiculo' => 'Sedán',
            'kilometraje' => 18500,
            'combustible' => 'Gasolina',
            'estado' => 'Usado - demostración',
            'proveedor_internacional' => 'Proveedor ficticio de demostración',
            'pais_compra' => 'Japón',
            'precio_adquisicion' => 14500,
            'moneda' => 'USD',
            'fecha_compra' => '2026-03-15',
            'incoterm' => 'FOB',
            'pais_origen' => 'Japón',
            'pais_procedencia' => 'Japón',
            'pais_destino' => 'Colombia',
            'puerto_salida' => 'Yokohama (ejemplo)',
            'puerto_llegada' => 'Cartagena (ejemplo)',
            'transportadora' => 'Transportadora internacional de demostración',
            'agencia_aduanas' => 'Agencia de aduanas de demostración',
            'fecha_embarque' => '2026-04-01',
            'fecha_llegada' => '2026-05-01',
            'numero_declaracion_importacion' => "DEMO-IMP-{$vehiculo->id}",
            'valor_fob' => 14500,
            'flete_internacional' => 1200,
            'seguro' => 175,
            'valor_cif' => 15875,
            'aranceles' => 500,
            'impuestos' => 1000,
            'gastos_aduaneros' => 250,
            'costo_total_importacion' => 17625,
        ];
    }
}
