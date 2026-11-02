<?php

namespace Tests\Feature;

use App\Models\Vehiculo;
use App\Models\TransaccionFinanciera;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CatalogoTest extends TestCase
{
    use RefreshDatabase;

    public function test_catalog_search_matches_model_and_hides_unavailable_vehicles(): void
    {
        $available = $this->createVehicle('Toyota', 'Corolla', true);
        $this->createVehicle('Toyota', 'Corolla Cross', false);

        $response = $this->get('/catalogo?marca=corolla');

        $response->assertOk()
            ->assertSee('Toyota Corolla')
            ->assertDontSee('Corolla Cross')
            ->assertSee(route('catalogo.show', $available->id));
    }

    public function test_vehicle_detail_is_available_only_for_available_vehicles(): void
    {
        $available = $this->createVehicle('Toyota', 'Corolla', true);
        $unavailable = $this->createVehicle('Toyota', 'Yaris', false);

        $this->get(route('catalogo.show', $available->id))
            ->assertOk()
            ->assertSee('Toyota')
            ->assertSee('Corolla');

        $this->get(route('catalogo.show', $unavailable->id))
            ->assertNotFound();
    }

    public function test_sold_vehicles_are_hidden_even_if_the_available_flag_is_stale(): void
    {
        $sold = $this->createVehicle('Toyota', 'Corolla', true);
        TransaccionFinanciera::create([
            'tipo' => 'ingreso',
            'monto' => 50000000,
            'moneda' => 'COP',
            'vehiculo_id' => $sold->id,
            'descripcion' => 'Venta de prueba',
            'fecha' => now()->toDateString(),
            'categoria' => 'Venta de vehículo',
        ]);

        $client = User::factory()->create(['role' => User::ROLE_EXTERNO]);

        $this->get('/catalogo')
            ->assertOk()
            ->assertDontSee('Toyota Corolla');

        $this->get(route('catalogo.show', $sold->id))
            ->assertNotFound();

        $this->actingAs($client)
            ->getJson("/api/vehiculos/{$sold->id}")
            ->assertNotFound()
            ->assertJsonPath('message', 'Vehículo no encontrado o no disponible.');
    }

    private function createVehicle(string $brand, string $model, bool $available): Vehiculo
    {
        return Vehiculo::create([
            'marca' => $brand,
            'modelo' => $model,
            'color' => 'Negro',
            'puertas' => 4,
            'hp' => 150,
            'precio_cliente' => 50000000,
            'costo_empresa' => 40000000,
            'disponible' => $available,
        ]);
    }
}
