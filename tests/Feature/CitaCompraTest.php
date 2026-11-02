<?php

namespace Tests\Feature;

use App\Models\Cita;
use App\Models\TransaccionFinanciera;
use App\Models\User;
use App\Models\Vehiculo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CitaCompraTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_request_is_assigned_to_accounting_and_appears_in_both_views(): void
    {
        $vehicle = $this->createVehicle();
        $client = User::factory()->create([
            'role' => User::ROLE_EXTERNO,
            'name' => 'Cliente de prueba',
            'phone' => '3005550101',
        ]);
        $accountant = User::factory()->create(['role' => User::ROLE_CONTADOR]);

        $this->actingAs($client)
            ->get(route('catalogo.compra.create', $vehicle))
            ->assertOk()
            ->assertSee('Agendar asesoría')
            ->assertSee('Cliente de prueba')
            ->assertSee('3005550101');

        $this->post(route('catalogo.compra.store', $vehicle), [
            'nombre_contacto' => 'Cliente de prueba',
            'telefono_contacto' => '+57 300 555 0101',
            'fecha_hora' => now()->addDays(3)->format('Y-m-d\TH:i'),
            'motivo' => 'Quiero conocer las opciones de financiación.',
        ])->assertRedirect(route('compras.mine'))
            ->assertSessionHas('success');

        $appointment = Cita::query()->firstOrFail();
        $this->assertSame($client->id, $appointment->user_id);
        $this->assertSame($accountant->id, $appointment->responsable_id);
        $this->assertSame($vehicle->id, $appointment->vehiculo_id);
        $this->assertSame('compra', $appointment->tipo);
        $this->assertSame('pendiente', $appointment->estado);
        $this->assertTrue($vehicle->fresh()->disponible);
        $this->assertDatabaseCount('transacciones_financieras', 0);

        $this->get(route('compras.mine'))
            ->assertOk()
            ->assertSee('Cliente de prueba')
            ->assertSee('Contabilidad');

        $this->actingAs($accountant)
            ->get(route('admin.compras.index'))
            ->assertOk()
            ->assertSee('Cliente de prueba')
            ->assertSee('+57 300 555 0101')
            ->assertSee('Quiero conocer las opciones de financiación.');

        $this->put(route('admin.compras.status', $appointment), ['estado' => 'confirmada'])
            ->assertRedirect(route('admin.compras.index'));

        $this->assertSame('confirmada', $appointment->fresh()->estado);
        $this->assertTrue($vehicle->fresh()->disponible);
        $this->assertDatabaseCount('transacciones_financieras', 0);
    }

    public function test_availability_changes_only_when_manually_disabled_or_sale_is_registered(): void
    {
        $vehicle = $this->createVehicle();
        $manager = User::factory()->create(['role' => User::ROLE_JEFE]);
        $client = User::factory()->create(['role' => User::ROLE_EXTERNO]);
        User::factory()->create(['role' => User::ROLE_CONTADOR]);

        $this->actingAs($manager)
            ->put(route('admin.vehiculos.update', $vehicle), [
                'marca' => $vehicle->marca,
                'modelo' => $vehicle->modelo,
                'color' => $vehicle->color,
                'puertas' => $vehicle->puertas,
                'hp' => $vehicle->hp,
                'precio_cliente' => $vehicle->precio_cliente,
                'costo_empresa' => $vehicle->costo_empresa,
                'disponible' => 0,
            ])
            ->assertRedirect(route('admin.vehiculos.index'));

        $this->assertFalse($vehicle->fresh()->disponible);
        $this->get('/catalogo')->assertDontSee('Toyota Corolla');

        $this->put(route('admin.vehiculos.update', $vehicle), [
            'marca' => $vehicle->marca,
            'modelo' => $vehicle->modelo,
            'color' => $vehicle->color,
            'puertas' => $vehicle->puertas,
            'hp' => $vehicle->hp,
            'precio_cliente' => $vehicle->precio_cliente,
            'costo_empresa' => $vehicle->costo_empresa,
            'disponible' => 1,
        ])->assertRedirect(route('admin.vehiculos.index'));

        $this->assertTrue($vehicle->fresh()->disponible);
        $this->get('/catalogo')->assertSee('Toyota Corolla');

        $this->actingAs($client)
            ->post(route('catalogo.compra.store', $vehicle), [
                'nombre_contacto' => 'Cliente de prueba',
                'telefono_contacto' => '3005550101',
                'fecha_hora' => now()->addDays(3)->format('Y-m-d\TH:i'),
            ])
            ->assertRedirect(route('compras.mine'));

        $this->assertTrue($vehicle->fresh()->disponible);

        $this->actingAs($manager)
            ->post(route('admin.vehiculos.venta', $vehicle), [
                'monto' => 50000000,
                'fecha' => now()->toDateString(),
            ])
            ->assertRedirect(route('admin.vehiculos.index'));

        $this->assertFalse($vehicle->fresh()->disponible);

        $this->put(route('admin.vehiculos.update', $vehicle), [
            'marca' => $vehicle->marca,
            'modelo' => $vehicle->modelo,
            'color' => $vehicle->color,
            'puertas' => $vehicle->puertas,
            'hp' => $vehicle->hp,
            'precio_cliente' => $vehicle->precio_cliente,
            'costo_empresa' => $vehicle->costo_empresa,
            'disponible' => 1,
        ])->assertRedirect(route('admin.vehiculos.index'));

        $this->assertFalse($vehicle->fresh()->disponible);
        $this->actingAs($client)->get('/catalogo')->assertDontSee('Toyota Corolla');
    }

    public function test_customer_cannot_request_a_sold_or_unavailable_vehicle(): void
    {
        $vehicle = $this->createVehicle();
        $client = User::factory()->create(['role' => User::ROLE_EXTERNO]);
        User::factory()->create(['role' => User::ROLE_CONTADOR]);
        $vehicle->update(['disponible' => false]);

        $this->actingAs($client)
            ->get(route('catalogo.compra.create', $vehicle))
            ->assertNotFound();

        $this->post(route('catalogo.compra.store', $vehicle), [
            'nombre_contacto' => 'Cliente de prueba',
            'telefono_contacto' => '3005550101',
            'fecha_hora' => now()->addDays(3)->format('Y-m-d\TH:i'),
        ])->assertSessionHasErrors('vehiculo_id');

        $this->assertDatabaseCount('citas', 0);

        $availableWithSale = $this->createVehicle();
        TransaccionFinanciera::create([
            'tipo' => 'ingreso',
            'monto' => 50000000,
            'moneda' => 'COP',
            'vehiculo_id' => $availableWithSale->id,
            'descripcion' => 'Venta de prueba',
            'fecha' => now()->toDateString(),
            'categoria' => 'Venta de vehículo',
        ]);

        $this->get(route('catalogo.compra.create', $availableWithSale))
            ->assertNotFound();
    }

    public function test_existing_customer_appointment_api_rejects_a_sold_vehicle_with_stale_availability(): void
    {
        $vehicle = $this->createVehicle();
        $client = User::factory()->create(['role' => User::ROLE_EXTERNO]);
        TransaccionFinanciera::create([
            'tipo' => 'ingreso',
            'monto' => 50000000,
            'moneda' => 'COP',
            'vehiculo_id' => $vehicle->id,
            'descripcion' => 'Venta de prueba',
            'fecha' => now()->toDateString(),
            'categoria' => 'Venta de vehículo',
        ]);

        $this->actingAs($client)
            ->postJson('/api/citas', [
                'vehiculo_id' => $vehicle->id,
                'fecha_hora' => now()->addDays(3)->toIso8601String(),
                'motivo' => 'Quiero agendar una cita para este vehículo.',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('vehiculo_id');

        $this->assertDatabaseCount('citas', 0);
    }

    public function test_purchase_request_requires_an_accountant_to_be_configured(): void
    {
        $vehicle = $this->createVehicle();
        $client = User::factory()->create(['role' => User::ROLE_EXTERNO]);

        $this->actingAs($client)
            ->post(route('catalogo.compra.store', $vehicle), [
                'nombre_contacto' => 'Cliente de prueba',
                'telefono_contacto' => '3005550101',
                'fecha_hora' => now()->addDays(3)->format('Y-m-d\TH:i'),
            ])
            ->assertSessionHasErrors('contacto');

        $this->assertDatabaseCount('citas', 0);
    }

    public function test_only_accounting_roles_can_manage_purchase_requests(): void
    {
        $vehicle = $this->createVehicle();
        $client = User::factory()->create(['role' => User::ROLE_EXTERNO]);
        $accountant = User::factory()->create(['role' => User::ROLE_CONTADOR]);
        $otherAccountant = User::factory()->create(['role' => User::ROLE_CONTADOR]);
        $appointment = Cita::query()->create([
            'user_id' => $client->id,
            'responsable_id' => $accountant->id,
            'vehiculo_id' => $vehicle->id,
            'tipo' => 'compra',
            'nombre_contacto' => 'Cliente',
            'telefono_contacto' => '3005550101',
            'fecha_hora' => now()->addDays(3),
            'motivo' => 'Asesoría de compra',
            'estado' => 'pendiente',
        ]);

        $this->actingAs($client)
            ->get(route('admin.compras.index'))
            ->assertRedirect(route('catalogo'));

        $this->actingAs($client)
            ->put(route('admin.compras.status', $appointment), ['estado' => 'confirmada'])
            ->assertRedirect(route('catalogo'));

        $this->actingAs($otherAccountant)
            ->get(route('admin.compras.index'))
            ->assertOk()
            ->assertDontSee('3005550101');

        $this->put(route('admin.compras.status', $appointment), ['estado' => 'confirmada'])
            ->assertNotFound();

        $this->actingAs($accountant)
            ->put(route('admin.compras.status', $appointment), ['estado' => 'invalid'])
            ->assertSessionHasErrors('estado');

        $this->assertSame('pendiente', $appointment->fresh()->estado);
    }

    private function createVehicle(): Vehiculo
    {
        return Vehiculo::query()->create([
            'marca' => 'Toyota',
            'modelo' => 'Corolla',
            'color' => 'Negro',
            'puertas' => 4,
            'hp' => 150,
            'precio_cliente' => 50000000,
            'costo_empresa' => 40000000,
            'disponible' => true,
        ]);
    }
}
