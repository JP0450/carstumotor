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
            ->get(route('compras.mine'))
            ->assertOk()
            ->assertSee('Hacer una solicitud')
            ->assertSee('href="'.route('catalogo').'"', false);

        $this->get(route('catalogo'))
            ->assertOk()
            ->assertSee('Agendar cita')
            ->assertSee('href="'.route('catalogo.compra.create', $vehicle).'"', false)
            ->assertSee('href="'.route('catalogo.show', $vehicle).'"', false);

        $this->get(route('catalogo.show', $vehicle))
            ->assertOk()
            ->assertSee('href="'.route('catalogo.compra.create', $vehicle).'"', false);

        $this->get(route('catalogo.compra.create', $vehicle))
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

    public function test_customer_can_confirm_attendance_without_changing_accounting_status(): void
    {
        $vehicle = $this->createVehicle();
        $client = User::factory()->create(['role' => User::ROLE_EXTERNO]);
        $accountant = User::factory()->create(['role' => User::ROLE_CONTADOR]);
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
            ->get(route('compras.mine'))
            ->assertOk()
            ->assertSee('Confirmar mi asistencia')
            ->assertSee('El mismo día de la asesoría ya no podrás cambiar la fecha ni la hora.');

        $this->post(route('compras.attendance.confirm', $appointment))
            ->assertRedirect(route('compras.mine'))
            ->assertSessionHas('success');

        $this->assertNotNull($appointment->fresh()->cliente_confirmo_at);
        $this->assertSame('pendiente', $appointment->fresh()->estado);

        $this->actingAs($accountant)
            ->get(route('admin.compras.index'))
            ->assertOk()
            ->assertSee('Asistencia del cliente')
            ->assertSee('Confirmó');
    }

    public function test_customer_can_change_schedule_before_appointment_day_and_must_confirm_again(): void
    {
        $vehicle = $this->createVehicle();
        $client = User::factory()->create(['role' => User::ROLE_EXTERNO]);
        $appointment = Cita::query()->create([
            'user_id' => $client->id,
            'vehiculo_id' => $vehicle->id,
            'tipo' => 'compra',
            'nombre_contacto' => 'Cliente',
            'telefono_contacto' => '3005550101',
            'fecha_hora' => now()->addDays(3),
            'motivo' => 'Asesoría de compra',
            'estado' => 'confirmada',
            'cliente_confirmo_at' => now(),
        ]);
        $newSchedule = now()->addDays(5)->setTime(15, 30);

        $this->actingAs($client)
            ->put(route('compras.schedule.update', $appointment), [
                'fecha_hora' => $newSchedule->format('Y-m-d\TH:i'),
            ])
            ->assertRedirect(route('compras.mine'))
            ->assertStatus(303)
            ->assertSessionHas('success');

        $appointment->refresh();
        $this->assertSame($newSchedule->format('Y-m-d H:i'), $appointment->fecha_hora->format('Y-m-d H:i'));
        $this->assertSame('pendiente', $appointment->estado);
        $this->assertNull($appointment->cliente_confirmo_at);

        $this->get(route('compras.mine'))
            ->assertOk()
            ->assertSee('El horario se actualizó.')
            ->assertSee('Cambiar fecha y hora')
            ->assertSee('Confirmar mi asistencia');
    }

    public function test_customer_cannot_change_schedule_on_the_appointment_day(): void
    {
        $this->travelTo(now()->startOfDay());
        $vehicle = $this->createVehicle();
        $client = User::factory()->create(['role' => User::ROLE_EXTERNO]);
        $appointmentSchedule = now()->setTime(15, 0);
        $appointment = Cita::query()->create([
            'user_id' => $client->id,
            'vehiculo_id' => $vehicle->id,
            'tipo' => 'compra',
            'nombre_contacto' => 'Cliente',
            'telefono_contacto' => '3005550101',
            'fecha_hora' => $appointmentSchedule,
            'motivo' => 'Asesoría de compra',
            'estado' => 'pendiente',
        ]);

        $this->actingAs($client)
            ->get(route('compras.mine'))
            ->assertOk()
            ->assertSee('Esta asesoría es hoy; ya no puedes modificar su horario.')
            ->assertDontSee(route('compras.schedule.update', $appointment), false);

        $this->from(route('compras.mine'))
            ->put(route('compras.schedule.update', $appointment), [
                'fecha_hora' => now()->addDay()->setTime(15, 0)->format('Y-m-d\TH:i'),
            ])
            ->assertRedirect(route('compras.mine'))
            ->assertSessionHasErrors([
                'fecha_hora' => 'No puedes cambiar la fecha ni la hora el mismo día de la asesoría.',
            ]);

        $this->assertSame(
            $appointmentSchedule->format('Y-m-d H:i'),
            $appointment->fresh()->fecha_hora->format('Y-m-d H:i')
        );
    }

    public function test_customer_cannot_use_an_existing_vehicle_time_slot_for_new_or_updated_requests(): void
    {
        $vehicle = $this->createVehicle();
        $client = User::factory()->create(['role' => User::ROLE_EXTERNO]);
        $otherClient = User::factory()->create(['role' => User::ROLE_EXTERNO]);
        User::factory()->create(['role' => User::ROLE_CONTADOR]);
        $existingSchedule = now()->addDays(3)->setTime(14, 0);
        $existingAppointment = Cita::query()->create([
            'user_id' => $otherClient->id,
            'vehiculo_id' => $vehicle->id,
            'tipo' => 'compra',
            'nombre_contacto' => 'Otro cliente',
            'telefono_contacto' => '3005550102',
            'fecha_hora' => $existingSchedule,
            'motivo' => 'Asesoría de compra',
            'estado' => 'pendiente',
        ]);
        $editableSchedule = now()->addDays(4)->setTime(14, 0);
        $editableAppointment = Cita::query()->create([
            'user_id' => $client->id,
            'vehiculo_id' => $vehicle->id,
            'tipo' => 'compra',
            'nombre_contacto' => 'Cliente',
            'telefono_contacto' => '3005550101',
            'fecha_hora' => $editableSchedule,
            'motivo' => 'Asesoría de compra',
            'estado' => 'pendiente',
        ]);
        $slot = $existingSchedule->format('Y-m-d\TH:i');

        $this->followingRedirects()
            ->actingAs($client)
            ->from(route('catalogo.compra.create', $vehicle))
            ->post(route('catalogo.compra.store', $vehicle), [
                'nombre_contacto' => 'Cliente',
                'telefono_contacto' => '3005550101',
                'fecha_hora' => $slot,
            ])
            ->assertOk()
            ->assertSee('Ese horario ya fue seleccionado para este vehículo. Elige otra fecha u hora.');

        $this->followingRedirects()
            ->from(route('compras.mine'))
            ->put(route('compras.schedule.update', $editableAppointment), [
                'fecha_hora' => $slot,
            ])
            ->assertOk()
            ->assertSee('Ese horario ya fue seleccionado para este vehículo. Elige otra fecha u hora.');

        $this->assertDatabaseCount('citas', 2);
        $this->assertSame(
            $existingSchedule->format('Y-m-d H:i'),
            $existingAppointment->fresh()->fecha_hora->format('Y-m-d H:i')
        );
        $this->assertSame(
            $editableSchedule->format('Y-m-d H:i'),
            $editableAppointment->fresh()->fecha_hora->format('Y-m-d H:i')
        );
    }

    public function test_customer_cannot_manage_another_customers_appointment(): void
    {
        $vehicle = $this->createVehicle();
        $owner = User::factory()->create(['role' => User::ROLE_EXTERNO]);
        $otherClient = User::factory()->create(['role' => User::ROLE_EXTERNO]);
        $appointment = Cita::query()->create([
            'user_id' => $owner->id,
            'vehiculo_id' => $vehicle->id,
            'tipo' => 'compra',
            'nombre_contacto' => 'Cliente',
            'telefono_contacto' => '3005550101',
            'fecha_hora' => now()->addDays(3),
            'motivo' => 'Asesoría de compra',
            'estado' => 'pendiente',
        ]);

        $this->actingAs($otherClient)
            ->post(route('compras.attendance.confirm', $appointment))
            ->assertNotFound();

        $this->put(route('compras.schedule.update', $appointment), [
            'fecha_hora' => now()->addDays(4)->format('Y-m-d\TH:i'),
        ])->assertNotFound();

        $this->assertNull($appointment->fresh()->cliente_confirmo_at);
        $this->assertSame('pendiente', $appointment->fresh()->estado);
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
