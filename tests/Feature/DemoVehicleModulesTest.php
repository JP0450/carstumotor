<?php

namespace Tests\Feature;

use App\Models\Inventario;
use App\Models\TransaccionFinanciera;
use App\Models\User;
use App\Models\Vehiculo;
use App\Models\VehiculoDocumento;
use App\Services\DashboardGerencialService;
use App\Services\FinanzaService;
use Database\Seeders\DemoDataSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DemoVehicleModulesTest extends TestCase
{
    use RefreshDatabase;

    public function test_all_authenticated_roles_can_open_the_demo_modules(): void
    {
        Storage::fake('local');
        $this->createVehicle();

        foreach (User::roles() as $role) {
            $user = User::factory()->create(['role' => $role]);

            $this->actingAs($user)
                ->get(route('admin.dashboard.gerencial'))
                ->assertOk()
                ->assertSee('Dashboard gerencial')
                ->assertSee('Resumen general · Mes actual')
                ->assertSee('Panel de consulta')
                ->assertSee('Indicadores adicionales')
                ->assertSee('0 ingreso(s) · 0 egreso(s) registrados')
                ->assertDontSee('185.000.000');

            $this->get(route('finanzas.index'))
                ->assertOk()
                ->assertSee('Finanzas y contabilidad')
                ->assertSee('Registrar movimiento')
                ->assertSee('Movimientos registrados por mes')
                ->assertSee('Importes no consolidados');

            $this->get(route('internacional.vehiculos.index'))
                ->assertOk()
                ->assertSee('Toyota Corolla');

            $vehiculo = Vehiculo::query()->firstOrFail();
            $this->put(route('internacional.vehiculos.update', $vehiculo), ['anio' => 2022])
                ->assertRedirect(route('internacional.vehiculos.edit', $vehiculo));

            $this->post(route('internacional.vehiculos.documentos.store', $vehiculo), [
                'tipo' => 'Factura comercial',
                'archivo' => UploadedFile::fake()->createWithContent('factura-demo.pdf', "%PDF-1.4\nDocumento ficticio\n%%EOF"),
            ])->assertRedirect(route('internacional.vehiculos.edit', $vehiculo));
        }

        $this->assertDatabaseCount('vehiculo_documentos', count(User::roles()));
    }

    public function test_all_authenticated_roles_can_create_update_and_delete_financial_movements(): void
    {
        foreach (User::roles() as $role) {
            $user = User::factory()->create(['role' => $role]);
            $this->actingAs($user);

            $this->post(route('finanzas.store'), [
                'tipo' => 'ingreso',
                'monto' => 125000,
                'moneda' => 'COP',
                'descripcion' => 'Ingreso de prueba para exposición',
                'fecha' => '2026-10-25',
                'categoria' => 'Venta',
            ])->assertRedirect(route('finanzas.index'));

            $transaccion = TransaccionFinanciera::query()->latest('id')->firstOrFail();

            $this->get(route('finanzas.index'))
                ->assertOk()
                ->assertSee('125.000,00')
                ->assertSee('COP')
                ->assertSee('Ingreso de prueba para exposición');

            $this->put(route('finanzas.update', $transaccion), [
                'tipo' => 'gasto',
                'monto' => 50000,
                'moneda' => 'USD',
                'descripcion' => 'Gasto actualizado para exposición',
                'fecha' => '2026-10-26',
                'categoria' => 'Administrativo',
            ])->assertRedirect(route('finanzas.index'));

            $this->assertDatabaseHas('transacciones_financieras', [
                'id' => $transaccion->id,
                'tipo' => 'gasto',
                'monto' => 50000,
                'moneda' => 'USD',
                'user_id' => $user->id,
            ]);

            $this->delete(route('finanzas.destroy', $transaccion))
                ->assertRedirect(route('finanzas.index'));

            $this->assertDatabaseMissing('transacciones_financieras', ['id' => $transaccion->id]);
        }
    }

    public function test_financial_movement_form_rejects_negative_or_zero_amounts(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_EXTERNO]);

        $this->actingAs($user)
            ->post(route('finanzas.store'), [
                'tipo' => 'ingreso',
                'monto' => 0,
                'moneda' => 'COP',
                'descripcion' => 'Monto no permitido',
                'fecha' => '2026-10-25',
                'categoria' => 'Venta',
            ])
            ->assertSessionHasErrors('monto');
    }

    public function test_financial_movement_can_store_currency_and_optional_vehicle_link(): void
    {
        $vehicle = $this->createVehicle();
        $user = User::factory()->create(['role' => User::ROLE_CONTADOR]);

        $this->actingAs($user)
            ->post(route('finanzas.store'), [
                'tipo' => 'gasto',
                'monto' => 18500000,
                'moneda' => 'COP',
                'vehiculo_id' => $vehicle->id,
                'descripcion' => 'Transporte internacional de prueba',
                'fecha' => now()->toDateString(),
                'categoria' => 'Transporte internacional',
            ])
            ->assertRedirect(route('finanzas.index'));

        $this->assertDatabaseHas('transacciones_financieras', [
            'tipo' => 'gasto',
            'monto' => 18500000,
            'moneda' => 'COP',
            'vehiculo_id' => $vehicle->id,
        ]);

        $this->get(route('finanzas.index'))
            ->assertOk()
            ->assertSee('COP 18.500.000,00')
            ->assertSee('Transporte internacional')
            ->assertSee('Toyota Corolla');
    }

    public function test_financial_reports_do_not_add_different_currencies_together(): void
    {
        TransaccionFinanciera::create([
            'tipo' => 'ingreso',
            'monto' => 1000,
            'moneda' => 'USD',
            'descripcion' => 'Ingreso en dólares de prueba',
            'fecha' => now()->toDateString(),
            'categoria' => 'Venta',
        ]);
        TransaccionFinanciera::create([
            'tipo' => 'gasto',
            'monto' => 500000,
            'moneda' => 'COP',
            'descripcion' => 'Gasto en pesos de prueba',
            'fecha' => now()->toDateString(),
            'categoria' => 'Operación',
        ]);

        $service = app(FinanzaService::class);
        $start = now()->startOfMonth()->toDateString();
        $end = now()->endOfMonth()->toDateString();
        $this->assertDatabaseCount('transacciones_financieras', 2);
        $this->assertDatabaseCount('transacciones_financieras', 2);

        $this->assertNull($service->obtenerFlujoCaja($start, $end)['entradas']);
        $this->assertSame(1000.0, $service->obtenerFlujoCaja($start, $end, 'USD')['entradas']);
        $this->assertNull($service->obtenerEstadoResultados($start, $end)['ingresos']);
        $this->assertSame(1000.0, $service->obtenerEstadoResultados($start, $end, 'USD')['ingresos']);
    }

    public function test_finance_monthly_chart_uses_real_movement_counts(): void
    {
        $today = now()->toDateString();
        foreach (['ingreso', 'ingreso', 'gasto', 'impuesto'] as $tipo) {
            TransaccionFinanciera::create([
                'tipo' => $tipo,
                'monto' => 100,
                'descripcion' => 'Movimiento mensual de prueba',
                'fecha' => $today,
                'categoria' => 'Prueba',
            ]);
        }

        $this->assertDatabaseCount('transacciones_financieras', 4);
        $serie = app(FinanzaService::class)->obtenerConteosMensualesMovimientos();

        $this->assertCount(6, $serie['labels']);
        $this->assertSame(2, $serie['ingresos'][5]);
        $this->assertSame(2, $serie['egresos'][5]);
        $this->assertSame(0, $serie['ingresos'][0]);
    }

    public function test_demo_seeder_populates_all_available_modules_idempotently_without_replacing_existing_records(): void
    {
        Storage::fake('local');
        $existingVehicle = $this->createVehicle([
            'marca' => 'Toyota',
            'modelo' => 'Corolla',
            'color' => 'Verde',
        ]);
        $originalPassword = getenv('DEMO_LOGIN_PASSWORD');
        putenv('DEMO_LOGIN_PASSWORD=temporary-demo-password-1234');

        try {
            $seeder = app(DemoDataSeeder::class);
            $seeder->run();
            $seeder->run();
        } finally {
            if ($originalPassword === false) {
                putenv('DEMO_LOGIN_PASSWORD');
            } else {
                putenv('DEMO_LOGIN_PASSWORD='.$originalPassword);
            }
        }

        $this->assertDatabaseCount('users', 4);
        $this->assertDatabaseCount('vehiculos', 9);
        $this->assertDatabaseCount('vehiculo_informacion_internacional', 8);
        $this->assertDatabaseCount('vehiculo_documentos', 8);
        $this->assertDatabaseCount('inventarios', 8);
        $this->assertDatabaseCount('citas', 2);
        $this->assertDatabaseCount('pedidos_internacionales', 2);
        $this->assertDatabaseCount('archivos_pedidos', 2);
        $this->assertDatabaseCount('transacciones_financieras', 25);
        $this->assertDatabaseCount('informes_financieros', 1);
        $this->assertDatabaseHas('vehiculos', [
            'id' => $existingVehicle->id,
            'modelo' => 'Corolla',
            'color' => 'Verde',
        ]);

        Storage::disk('local')->assertExists('demo/documento-demostracion.pdf');
        $this->assertStringStartsWith('%PDF-', Storage::disk('local')->get('demo/documento-demostracion.pdf'));
        $this->assertDatabaseHas('transacciones_financieras', [
            'tipo' => 'cuenta_por_cobrar',
            'descripcion' => 'DEMO | Cuenta por cobrar - cliente de ejemplo',
        ]);
        $this->assertDatabaseHas('vehiculos', [
            'marca' => 'BMW',
            'modelo' => 'M4 Competition Demo',
            'imagen' => '/images/demo/coupe.svg',
        ]);
        $this->assertDatabaseHas('vehiculo_informacion_internacional', [
            'moneda' => 'EUR',
            'pais_compra' => 'Alemania',
            'valor_fob' => 68000,
        ]);
        $this->assertDatabaseHas('transacciones_financieras', [
            'tipo' => 'gasto',
            'monto' => 18500000,
            'moneda' => 'COP',
            'vehiculo_id' => Vehiculo::query()->where('modelo', 'M4 Competition Demo')->value('id'),
            'descripcion' => 'DEMO | Transporte internacional del BMW M4 Competition',
        ]);
        $this->assertDatabaseHas('transacciones_financieras', [
            'tipo' => 'ingreso',
            'moneda' => 'COP',
            'descripcion' => 'DEMO | Ingreso de venta - '.now()->startOfMonth()->format('Y-m'),
        ]);
        $this->assertDatabaseHas('transacciones_financieras', [
            'tipo' => 'ingreso',
            'monto' => 158000000,
            'moneda' => 'COP',
            'categoria' => 'Venta de vehículo',
            'vehiculo_id' => Vehiculo::query()->where('modelo', 'Sportage Demo')->value('id'),
        ]);

        $dashboard = app(DashboardGerencialService::class)->obtenerDashboard('mes_actual');
        $this->assertSame(343000000.0, $dashboard['kpis']['ingresos']['valor']);
        $this->assertSame(1, $dashboard['kpis']['vehiculos_vendidos']['valor']);
        $this->assertNotEmpty($dashboard['finanzas']['rentabilidadMarcas']['labels']);
        $this->assertNotEmpty($dashboard['finanzas']['ventas']['cantidades']);
        $this->assertNotNull($dashboard['kpis']['utilidad_neta']['valor']);
        foreach ($dashboard['kpis'] as $kpi) {
            $this->assertNotNull($kpi['valor']);
        }
    }

    public function test_authenticated_roles_can_manage_financial_movements_through_the_api(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_NEGOCIOS]);

        $response = $this->actingAs($user)->postJson('/api/finanzas/transacciones', [
            'tipo' => 'ingreso',
            'monto' => 85000,
            'moneda' => 'COP',
            'descripcion' => 'Ingreso API para exposición',
            'fecha' => '2026-10-25',
            'categoria' => 'Servicio',
        ]);

        $response->assertCreated();
        $transaccionId = $response->json('id');

        $this->getJson('/api/finanzas/transacciones')
            ->assertOk()
            ->assertJsonPath('data.0.id', $transaccionId);

        $this->putJson("/api/finanzas/transacciones/{$transaccionId}", [
            'tipo' => 'gasto',
            'monto' => 40000,
            'moneda' => 'USD',
            'descripcion' => 'Gasto API para exposición',
            'fecha' => '2026-10-26',
            'categoria' => 'Operación',
        ])->assertOk()
            ->assertJsonPath('tipo', 'gasto');

        $this->deleteJson("/api/finanzas/transacciones/{$transaccionId}")
            ->assertNoContent();
    }

    public function test_manager_dashboard_aggregates_real_transactions_for_selected_period(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_JEFE]);
        TransaccionFinanciera::create([
            'tipo' => 'ingreso',
            'monto' => 1500,
            'moneda' => 'COP',
            'descripcion' => 'Registro de prueba con información privada',
            'fecha' => now()->subDays(5)->toDateString(),
            'categoria' => 'venta',
        ]);
        TransaccionFinanciera::create([
            'tipo' => 'gasto',
            'monto' => 300,
            'moneda' => 'COP',
            'descripcion' => 'Gasto de prueba',
            'fecha' => now()->subDays(4)->toDateString(),
            'categoria' => 'operacion',
        ]);
        TransaccionFinanciera::create([
            'tipo' => 'ingreso',
            'monto' => 9000,
            'moneda' => 'COP',
            'descripcion' => 'Ingreso fuera del periodo',
            'fecha' => now()->subDays(45)->toDateString(),
            'categoria' => 'venta',
        ]);

        $this->actingAs($user)
            ->get(route('admin.dashboard.gerencial', ['periodo' => '30_dias']))
            ->assertOk()
            ->assertSee('1 ingreso(s) · 1 egreso(s) registrados')
            ->assertSee('COP 1.500,00')
            ->assertDontSee('9.000,00')
            ->assertDontSee('Registro de prueba con información privada');
    }

    public function test_manager_dashboard_supports_custom_period_and_exports_summarized_csv(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_NEGOCIOS]);
        TransaccionFinanciera::create([
            'tipo' => 'ingreso',
            'monto' => 2500,
            'moneda' => 'COP',
            'descripcion' => 'Dato confidencial de cliente',
            'fecha' => '2026-10-12',
            'categoria' => 'otros',
        ]);

        $this->actingAs($user)
            ->get(route('admin.dashboard.gerencial', [
                'periodo' => 'personalizado',
                'fecha_inicio' => '2026-10-01',
                'fecha_fin' => '2026-10-20',
            ]))
            ->assertOk()
            ->assertSee('2026-10-01')
            ->assertSee('2026-10-20')
            ->assertSee('1 ingreso(s) · 0 egreso(s) registrados');

        $response = $this->actingAs($user)
            ->get(route('admin.dashboard.gerencial.csv', ['periodo' => 'personalizado', 'fecha_inicio' => '2026-10-01', 'fecha_fin' => '2026-10-20']));

        $response->assertOk()
            ->assertHeader('content-type', 'text/csv; charset=UTF-8');

        $csv = $response->streamedContent();
        $this->assertStringContainsString('ingresos', $csv);
        $this->assertStringContainsString('Ingresos COP', $csv);
        $this->assertStringContainsString(';2500;', $csv);
        $this->assertStringNotContainsString('Dato confidencial de cliente', $csv);
    }

    public function test_manager_dashboard_includes_sqlite_datetime_values_on_the_period_end_date(): void
    {
        $vehicle = $this->createVehicle(['marca' => 'Kia']);
        $fecha = now()->toDateString().' 00:00:00';

        foreach ([
            ['tipo' => 'ingreso', 'monto' => 500, 'categoria' => 'Servicio', 'vehiculo_id' => null],
            ['tipo' => 'ingreso', 'monto' => 1500, 'categoria' => 'Venta de vehículo', 'vehiculo_id' => $vehicle->id],
            ['tipo' => 'costo_venta', 'monto' => 700, 'categoria' => 'Costo de venta', 'vehiculo_id' => $vehicle->id],
        ] as $movimiento) {
            DB::table('transacciones_financieras')->insert($movimiento + [
                'moneda' => 'COP',
                'descripcion' => 'Movimiento demo con fecha y hora',
                'fecha' => $fecha,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $dashboard = app(DashboardGerencialService::class)->obtenerDashboard('mes_actual');

        $this->assertSame(2000.0, $dashboard['kpis']['ingresos']['valor']);
        $this->assertSame(1300.0, $dashboard['kpis']['utilidad_neta']['valor']);
        $this->assertSame(1, $dashboard['kpis']['vehiculos_vendidos']['valor']);
        $this->assertSame([1], $dashboard['finanzas']['ventas']['cantidades']);
        $this->assertSame(['Kia'], $dashboard['finanzas']['rentabilidadMarcas']['labels']);
    }

    public function test_manager_dashboard_rejects_invalid_or_incomplete_date_filters(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_EXTERNO]);

        $this->actingAs($user)
            ->get(route('admin.dashboard.gerencial', ['periodo' => 'personalizado']))
            ->assertSessionHasErrors(['fecha_inicio', 'fecha_fin']);

        $this->get(route('admin.dashboard.gerencial', ['periodo' => 'inventado']))
            ->assertSessionHasErrors('periodo');
    }

    public function test_manager_dashboard_handles_vehicle_boolean_fields_in_inventory_queries(): void
    {
        $availableVehicle = $this->createVehicle(['disponible' => true]);
        $unavailableVehicle = $this->createVehicle(['disponible' => false]);

        Inventario::create(['vehiculo_id' => $availableVehicle->id, 'cantidad' => 2]);
        Inventario::create(['vehiculo_id' => $unavailableVehicle->id, 'cantidad' => 5]);

        $dashboard = app(DashboardGerencialService::class)->obtenerDashboard('mes_actual');

        $this->assertSame(2, $dashboard['inventario']['disponibles']);
        $this->assertSame(5, $dashboard['inventario']['noDisponibles']);
        $this->assertSame(1, $dashboard['catalogo']['disponibles']);
        $this->assertSame(1, $dashboard['catalogo']['noDisponibles']);
    }

    public function test_manager_can_download_a_pdf_summary_for_the_selected_period(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_CONTADOR]);

        $response = $this->actingAs($user)
            ->get(route('admin.dashboard.gerencial.pdf', ['periodo' => '7_dias']));

        $response->assertOk()
            ->assertHeader('content-type', 'application/pdf');
        $this->assertStringStartsWith('%PDF-', $response->getContent());
    }

    public function test_vehicle_form_suggests_existing_values_and_saves_secondary_color(): void
    {
        $this->createVehicle([
            'marca' => 'Toyota',
            'modelo' => 'Corolla',
            'color' => 'Negro',
            'color_secundario' => 'Plata',
        ]);
        $user = User::factory()->create(['role' => User::ROLE_JEFE]);

        $this->actingAs($user)
            ->get(route('admin.vehiculos.create'))
            ->assertOk()
            ->assertSee('list="marcas-options"', false)
            ->assertSee('list="modelos-options"', false)
            ->assertSee('list="colores-options"', false)
            ->assertSee('Toyota')
            ->assertSee('Corolla')
            ->assertSee('Plata');

        $this->post(route('admin.vehiculos.store'), [
            'marca' => 'Toyota',
            'modelo' => 'Yaris',
            'color' => 'Blanco',
            'color_secundario' => 'Negro',
            'puertas' => 4,
            'hp' => 120,
            'precio_cliente' => 50000000,
            'costo_empresa' => 40000000,
            'disponible' => 1,
        ])->assertRedirect(route('admin.vehiculos.index'));

        $this->assertDatabaseHas('vehiculos', [
            'modelo' => 'Yaris',
            'color_secundario' => 'Negro',
        ]);
    }

    public function test_vehicle_international_information_and_private_pdf_documents_can_be_managed(): void
    {
        Storage::fake('local');
        $vehiculo = $this->createVehicle();
        $user = User::factory()->create(['role' => User::ROLE_EXTERNO]);
        $this->actingAs($user);

        $this->get(route('internacional.vehiculos.edit', $vehiculo))
            ->assertOk()
            ->assertSee('Ficha del vehículo')
            ->assertSee('Información del vehículo')
            ->assertSee('Información comercial')
            ->assertSee('Importación y logística')
            ->assertSee('Costos y valores')
            ->assertSee('Documentos adjuntos')
            ->assertSee('class="sheet-photo"', false)
            ->assertSee('DEMO-VIN-'.$vehiculo->id);

        $this->assertDatabaseCount('vehiculo_informacion_internacional', 0);

        $this->put(route('internacional.vehiculos.update', $vehiculo), [
            'anio' => 2022,
            'version' => 'Edición de demostración',
            'vin' => 'VIN-DEMO-0001',
            'numero_motor' => 'MOTOR-DEMO-0001',
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
            'puerto_salida' => 'Puerto de demostración',
            'puerto_llegada' => 'Puerto de demostración',
            'transportadora' => 'Transportadora ficticia',
            'agencia_aduanas' => 'Agencia ficticia',
            'fecha_embarque' => '2026-04-01',
            'fecha_llegada' => '2026-05-01',
            'numero_declaracion_importacion' => 'DEMO-IMP-0001',
            'valor_fob' => 14500,
            'flete_internacional' => 1200,
            'seguro' => 175,
            'valor_cif' => 15875,
            'aranceles' => 500,
            'impuestos' => 1000,
            'gastos_aduaneros' => 250,
            'costo_total_importacion' => 17625,
        ])->assertRedirect(route('internacional.vehiculos.edit', $vehiculo));

        $this->assertDatabaseHas('vehiculo_informacion_internacional', [
            'vehiculo_id' => $vehiculo->id,
            'vin' => 'VIN-DEMO-0001',
            'costo_total_importacion' => 17625,
        ]);

        $this->post(route('internacional.vehiculos.documentos.store', $vehiculo), [
            'tipo' => 'Factura comercial',
            'archivo' => UploadedFile::fake()->createWithContent('factura-demo.pdf', "%PDF-1.4\nDocumento ficticio de demostración\n%%EOF"),
        ])->assertRedirect(route('internacional.vehiculos.edit', $vehiculo));

        $documento = VehiculoDocumento::query()->firstOrFail();
        Storage::disk('local')->assertExists($documento->ruta);

        $this->get(route('internacional.vehiculos.documentos.show', [$vehiculo, $documento]))
            ->assertOk()
            ->assertHeader('Content-Type', 'application/pdf');

        $this->get(route('internacional.vehiculos.documentos.download', [$vehiculo, $documento]))
            ->assertOk()
            ->assertHeader('content-disposition', 'attachment; filename=factura-demo.pdf');

        $this->delete(route('internacional.vehiculos.documentos.destroy', [$vehiculo, $documento]))
            ->assertRedirect(route('internacional.vehiculos.edit', $vehiculo));

        Storage::disk('local')->assertMissing($documento->ruta);
        $this->assertDatabaseMissing('vehiculo_documentos', ['id' => $documento->id]);
    }

    public function test_document_categories_and_pdf_files_are_validated(): void
    {
        $vehiculo = $this->createVehicle();
        $user = User::factory()->create(['role' => User::ROLE_CONTADOR]);

        $this->actingAs($user)
            ->post(route('internacional.vehiculos.documentos.store', $vehiculo), [
                'tipo' => 'Documento inventado',
                'archivo' => UploadedFile::fake()->create('archivo.txt', 10, 'text/plain'),
            ])
            ->assertSessionHasErrors(['tipo', 'archivo']);
    }

    public function test_deleting_a_vehicle_removes_its_private_document_files(): void
    {
        Storage::fake('local');
        $vehiculo = $this->createVehicle();
        $user = User::factory()->create(['role' => User::ROLE_JEFE]);
        $ruta = UploadedFile::fake()
            ->createWithContent('contrato-demo.pdf', "%PDF-1.4\nDocumento ficticio\n%%EOF")
            ->store("vehiculos/{$vehiculo->id}/documentos", 'local');
        $vehiculo->documentos()->create([
            'tipo' => 'Contrato de compraventa',
            'nombre_archivo' => 'contrato-demo.pdf',
            'ruta' => $ruta,
        ]);

        $this->actingAs($user)
            ->delete(route('admin.vehiculos.destroy', $vehiculo))
            ->assertRedirect(route('admin.vehiculos.index'));

        Storage::disk('local')->assertMissing($ruta);
        $this->assertDatabaseMissing('vehiculos', ['id' => $vehiculo->id]);
        $this->assertDatabaseMissing('vehiculo_documentos', ['ruta' => $ruta]);
    }

    private function createVehicle(array $attributes = []): Vehiculo
    {
        return Vehiculo::create(array_merge([
            'marca' => 'Toyota',
            'modelo' => 'Corolla',
            'color' => 'Negro',
            'puertas' => 4,
            'hp' => 150,
            'precio_cliente' => 50000000,
            'costo_empresa' => 40000000,
            'disponible' => true,
        ], $attributes));
    }
}
