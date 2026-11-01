<?php

namespace Tests\Unit;

use App\Interfaces\IFinanzaRepository;
use App\Interfaces\IInventarioRepository;
use App\Interfaces\IVehiculoRepository;
use App\Models\Inventario;
use App\Models\TransaccionFinanciera;
use App\Services\DashboardEjecutivoService;
use App\Services\InventarioService;
use Illuminate\Database\Eloquent\Collection;
use Mockery;
use Tests\TestCase;

class BusinessLogicTest extends TestCase
{
    public function test_executive_net_profit_subtracts_sales_costs_expenses_and_taxes(): void
    {
        $finanzaRepository = Mockery::mock(IFinanzaRepository::class);
        $finanzaRepository->shouldReceive('findTransaccionesByTipo')
            ->once()->with('ingreso')->andReturn($this->transactions('ingreso', 1000));
        $finanzaRepository->shouldReceive('findTransaccionesByTipo')
            ->once()->with('gasto')->andReturn($this->transactions('gasto', 100));
        $finanzaRepository->shouldReceive('findTransaccionesByTipo')
            ->once()->with('costo_venta')->andReturn($this->transactions('costo_venta', 200));
        $finanzaRepository->shouldReceive('findTransaccionesByTipo')
            ->once()->with('impuesto')->andReturn($this->transactions('impuesto', 50));

        $service = new DashboardEjecutivoService(
            $finanzaRepository,
            Mockery::mock(IVehiculoRepository::class)
        );

        $kpis = $service->obtenerKPIs();

        $this->assertSame(650.0, $kpis['utilidades_netas']);
        $this->assertSame(80.0, $kpis['margen_bruto']);
        $this->assertSame('COP', $kpis['moneda']);
    }

    public function test_inventory_entry_updates_existing_location_when_provided(): void
    {
        $inventory = new Inventario(['cantidad' => 5, 'ubicacion' => 'Bodega A']);
        $repository = Mockery::mock(IInventarioRepository::class);
        $repository->shouldReceive('findByVehiculo')
            ->once()->with(12)->andReturn($inventory);
        $repository->shouldReceive('update')
            ->once()->with($inventory, ['cantidad' => 8, 'ubicacion' => 'Bodega B'])
            ->andReturn(true);

        $result = (new InventarioService($repository))->registrarEntrada(12, 3, 'Bodega B');

        $this->assertSame($inventory, $result);
    }

    private function transactions(string $type, float $amount): Collection
    {
        return new Collection([
            new TransaccionFinanciera(['tipo' => $type, 'monto' => $amount, 'moneda' => 'COP']),
        ]);
    }
}
