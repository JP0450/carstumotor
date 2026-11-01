<?php

namespace Tests\Feature;

use App\Models\Vehiculo;
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
