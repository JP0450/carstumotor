<?php

namespace Database\Seeders;

use App\Models\ArchivoPedido;
use App\Models\Cita;
use App\Models\InformeFinanciero;
use App\Models\Inventario;
use App\Models\PedidoInternacional;
use App\Models\TransaccionFinanciera;
use App\Models\User;
use App\Models\Vehiculo;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class DemoDataSeeder extends Seeder
{
    private const USERS = [
        'demo.jefe@carstumotor.test' => ['Gerencia Demo', User::ROLE_JEFE],
        'demo.contador@carstumotor.test' => ['Contabilidad Demo', User::ROLE_CONTADOR],
        'demo.internacional@carstumotor.test' => ['Comercio Internacional Demo', User::ROLE_NEGOCIOS],
        'demo.cliente@carstumotor.test' => ['Cliente Demo', User::ROLE_EXTERNO],
    ];

    public function run(): void
    {
        if (DB::connection()->getDriverName() !== 'sqlite') {
            throw new RuntimeException('Los datos de demostración solo se pueden cargar en una base SQLite local.');
        }

        $password = (string) env('DEMO_LOGIN_PASSWORD');
        if (strlen($password) < 16) {
            throw new RuntimeException('Ejecuta scripts/run-demo.ps1 para crear contraseñas locales de demostración.');
        }

        $pdfPath = 'demo/documento-demostracion.pdf';
        $storage = Storage::disk('local');
        if (! $storage->put($pdfPath, $this->pdfDemostracion())) {
            throw new RuntimeException('No se pudo guardar el PDF de demostración.');
        }

        DB::transaction(function () use ($password, $pdfPath): void {
            $users = [];
            foreach (self::USERS as $email => [$name, $role]) {
                $users[$role] = User::updateOrCreate(
                    ['email' => $email],
                    [
                        'name' => $name,
                        'role' => $role,
                        'email_verified_at' => now(),
                        'password' => Hash::make($password),
                    ]
                );
            }

            $vehicles = $this->seedVehicles($users[User::ROLE_CONTADOR], $pdfPath);
            $this->seedInventory($vehicles);
            $this->seedAppointments($users[User::ROLE_EXTERNO], $vehicles);
            $this->seedInternationalOrders($users[User::ROLE_NEGOCIOS], $pdfPath);
            $this->seedFinancialMovements($users[User::ROLE_CONTADOR]);
            $this->seedFinancialReport($users[User::ROLE_CONTADOR], $pdfPath);
        });

        $this->command?->info('Datos demo agregados de forma idempotente. No se modificaron ni eliminaron registros existentes.');
    }

    private function seedVehicles(User $user, string $pdfPath): array
    {
        $samples = [
            [
                'marca' => 'Toyota',
                'modelo' => 'Corolla Demo',
                'color' => 'Gris',
                'color_secundario' => 'Negro',
                'puertas' => 4,
                'hp' => 169,
                'precio_cliente' => 128000000,
                'costo_empresa' => 105000000,
                'disponible' => true,
                'stock' => 5,
                'ubicacion' => 'Bodega principal - Demo',
                'anio' => 2023,
                'vin' => 'DEMO-TOYOTA-COROLLA-001',
                'imagen' => '/images/demo/sedan.svg',
            ],
            [
                'marca' => 'Mazda',
                'modelo' => 'CX-5 Demo',
                'color' => 'Rojo',
                'color_secundario' => 'Negro',
                'puertas' => 5,
                'hp' => 187,
                'precio_cliente' => 165000000,
                'costo_empresa' => 138000000,
                'disponible' => true,
                'stock' => 2,
                'ubicacion' => 'Bodega principal - Demo',
                'anio' => 2024,
                'vin' => 'DEMO-MAZDA-CX5-002',
                'imagen' => '/images/demo/suv.svg',
            ],
            [
                'marca' => 'Hyundai',
                'modelo' => 'Tucson Demo',
                'color' => 'Blanco',
                'color_secundario' => 'Gris',
                'puertas' => 5,
                'hp' => 187,
                'precio_cliente' => 172000000,
                'costo_empresa' => 144000000,
                'disponible' => true,
                'stock' => 3,
                'ubicacion' => 'Bodega norte - Demo',
                'anio' => 2023,
                'vin' => 'DEMO-HYUNDAI-TUCSON-003',
                'imagen' => '/images/demo/suv.svg',
            ],
            [
                'marca' => 'Kia',
                'modelo' => 'Sportage Demo',
                'color' => 'Azul',
                'color_secundario' => 'Negro',
                'puertas' => 5,
                'hp' => 187,
                'precio_cliente' => 158000000,
                'costo_empresa' => 132000000,
                'disponible' => false,
                'stock' => 1,
                'ubicacion' => 'Zona de entrega - Demo',
                'anio' => 2022,
                'vin' => 'DEMO-KIA-SPORTAGE-004',
                'imagen' => '/images/demo/suv.svg',
            ],
            [
                'marca' => 'BMW',
                'modelo' => 'M4 Competition Demo',
                'color' => 'Negro',
                'color_secundario' => 'Rojo',
                'puertas' => 2,
                'hp' => 503,
                'precio_cliente' => 450000000,
                'costo_empresa' => 375000000,
                'disponible' => true,
                'stock' => 1,
                'ubicacion' => 'Bodega principal - Demo',
                'anio' => 2025,
                'vin' => 'DEMO-BMW-M4-005',
                'imagen' => '/images/demo/coupe.svg',
                'tipo_vehiculo' => 'Coupé',
                'combustible' => 'Gasolina',
                'pais_compra' => 'Alemania',
                'moneda' => 'EUR',
                'precio_adquisicion' => 68000,
                'valor_fob' => 68000,
                'flete_internacional' => 2800,
                'seguro' => 850,
                'valor_cif' => 71650,
                'aranceles' => 10748,
                'impuestos' => 13614,
                'gastos_aduaneros' => 1600,
                'costo_total_importacion' => 97612,
                'puerto_salida' => 'Hamburgo - Demo',
            ],
            [
                'marca' => 'Honda',
                'modelo' => 'CR-V Touring Demo',
                'color' => 'Blanco',
                'color_secundario' => 'Negro',
                'puertas' => 5,
                'hp' => 190,
                'precio_cliente' => 198000000,
                'costo_empresa' => 164000000,
                'disponible' => true,
                'stock' => 2,
                'ubicacion' => 'Bodega norte - Demo',
                'anio' => 2024,
                'vin' => 'DEMO-HONDA-CRV-006',
                'imagen' => '/images/demo/suv.svg',
                'tipo_vehiculo' => 'SUV',
                'combustible' => 'Gasolina',
                'pais_compra' => 'Japón',
                'moneda' => 'JPY',
                'precio_adquisicion' => 4200000,
                'valor_fob' => 4200000,
                'flete_internacional' => 180000,
                'seguro' => 52000,
                'valor_cif' => 4432000,
                'aranceles' => 664800,
                'impuestos' => 842080,
                'gastos_aduaneros' => 90000,
                'costo_total_importacion' => 6028880,
                'puerto_salida' => 'Yokohama - Demo',
            ],
            [
                'marca' => 'Ford',
                'modelo' => 'Mustang GT Demo',
                'color' => 'Azul',
                'color_secundario' => 'Negro',
                'puertas' => 2,
                'hp' => 480,
                'precio_cliente' => 320000000,
                'costo_empresa' => 268000000,
                'disponible' => true,
                'stock' => 1,
                'ubicacion' => 'Bodega principal - Demo',
                'anio' => 2024,
                'vin' => 'DEMO-FORD-MUSTANG-007',
                'imagen' => '/images/demo/coupe.svg',
                'tipo_vehiculo' => 'Coupé',
                'combustible' => 'Gasolina',
                'pais_compra' => 'Estados Unidos',
                'moneda' => 'USD',
                'precio_adquisicion' => 52000,
                'valor_fob' => 52000,
                'flete_internacional' => 2100,
                'seguro' => 650,
                'valor_cif' => 54750,
                'aranceles' => 8213,
                'impuestos' => 10298,
                'gastos_aduaneros' => 1200,
                'costo_total_importacion' => 74461,
                'puerto_salida' => 'Miami - Demo',
            ],
            [
                'marca' => 'Kia',
                'modelo' => 'EV6 GT-Line Demo',
                'color' => 'Verde',
                'color_secundario' => 'Gris',
                'puertas' => 5,
                'hp' => 320,
                'precio_cliente' => 235000000,
                'costo_empresa' => 198000000,
                'disponible' => true,
                'stock' => 1,
                'ubicacion' => 'Bodega norte - Demo',
                'anio' => 2025,
                'vin' => 'DEMO-KIA-EV6-008',
                'imagen' => '/images/demo/electric.svg',
                'tipo_vehiculo' => 'SUV eléctrico',
                'combustible' => 'Eléctrico',
                'pais_compra' => 'Corea del Sur',
                'moneda' => 'USD',
                'precio_adquisicion' => 44500,
                'valor_fob' => 44500,
                'flete_internacional' => 1900,
                'seguro' => 560,
                'valor_cif' => 46960,
                'aranceles' => 7044,
                'impuestos' => 8453,
                'gastos_aduaneros' => 1100,
                'costo_total_importacion' => 63557,
                'puerto_salida' => 'Busan - Demo',
            ],
        ];

        $vehicles = [];
        foreach ($samples as $sample) {
            $vehicle = Vehiculo::firstOrCreate(
                ['marca' => $sample['marca'], 'modelo' => $sample['modelo']],
                [
                    'color' => $sample['color'],
                    'color_secundario' => $sample['color_secundario'],
                    'puertas' => $sample['puertas'],
                    'hp' => $sample['hp'],
                    'imagen' => $sample['imagen'],
                    'precio_cliente' => $sample['precio_cliente'],
                    'costo_empresa' => $sample['costo_empresa'],
                    'disponible' => $sample['disponible'],
                ]
            );
            if (! $vehicle->imagen) {
                $vehicle->update(['imagen' => $sample['imagen']]);
            }
            $vehicles[] = $vehicle;

            $vehicle->informacionInternacional()->firstOrCreate([], [
                'anio' => $sample['anio'],
                'version' => 'Edición de demostración',
                'vin' => $sample['vin'],
                'numero_motor' => 'DEMO-MOTOR-'.$vehicle->id,
                'tipo_vehiculo' => $sample['tipo_vehiculo'] ?? (str_contains($sample['modelo'], 'CX') || str_contains($sample['modelo'], 'Tucson') || str_contains($sample['modelo'], 'Sportage') ? 'SUV' : 'Sedán'),
                'kilometraje' => 18500,
                'combustible' => $sample['combustible'] ?? 'Gasolina',
                'estado' => 'Usado - demostración',
                'proveedor_internacional' => 'Proveedor ficticio de demostración',
                'pais_compra' => $sample['pais_compra'] ?? 'Japón',
                'precio_adquisicion' => $sample['precio_adquisicion'] ?? 14500,
                'moneda' => $sample['moneda'] ?? 'USD',
                'fecha_compra' => now()->subMonths(7)->toDateString(),
                'incoterm' => 'FOB',
                'pais_origen' => $sample['pais_compra'] ?? 'Japón',
                'pais_procedencia' => $sample['pais_compra'] ?? 'Japón',
                'pais_destino' => 'Colombia',
                'puerto_salida' => $sample['puerto_salida'] ?? 'Yokohama - Demo',
                'puerto_llegada' => 'Cartagena - Demo',
                'transportadora' => 'Transportadora internacional demo',
                'agencia_aduanas' => 'Agencia de aduanas demo',
                'fecha_embarque' => now()->subMonths(6)->toDateString(),
                'fecha_llegada' => now()->subMonths(5)->toDateString(),
                'numero_declaracion_importacion' => 'DEMO-IMP-'.$vehicle->id,
                'valor_fob' => $sample['valor_fob'] ?? 14500,
                'flete_internacional' => $sample['flete_internacional'] ?? 1200,
                'seguro' => $sample['seguro'] ?? 175,
                'valor_cif' => $sample['valor_cif'] ?? 15875,
                'aranceles' => $sample['aranceles'] ?? 2381,
                'impuestos' => $sample['impuestos'] ?? 3016,
                'gastos_aduaneros' => $sample['gastos_aduaneros'] ?? 550,
                'costo_total_importacion' => $sample['costo_total_importacion'] ?? 21822,
            ]);

            $vehicle->documentos()->firstOrCreate(
                [
                    'tipo' => 'Factura comercial',
                    'nombre_archivo' => 'demo-factura-comercial.pdf',
                ],
                [
                    'user_id' => $user->id,
                    'ruta' => $pdfPath,
                ]
            );
        }

        return $vehicles;
    }

    private function seedInventory(array $vehicles): void
    {
        foreach ($vehicles as $vehicle) {
            $location = match ($vehicle->modelo) {
                'Corolla Demo' => 'Bodega principal - Demo',
                'CX-5 Demo' => 'Bodega principal - Demo',
                'Tucson Demo' => 'Bodega norte - Demo',
                'M4 Competition Demo' => 'Bodega principal - Demo',
                'CR-V Touring Demo' => 'Bodega norte - Demo',
                'Mustang GT Demo' => 'Bodega principal - Demo',
                'EV6 GT-Line Demo' => 'Bodega norte - Demo',
                default => 'Zona de entrega - Demo',
            };
            $quantity = match ($vehicle->modelo) {
                'Corolla Demo' => 5,
                'CX-5 Demo' => 2,
                'Tucson Demo' => 3,
                'M4 Competition Demo' => 1,
                'CR-V Touring Demo' => 2,
                'Mustang GT Demo' => 1,
                'EV6 GT-Line Demo' => 1,
                default => 1,
            };

            Inventario::firstOrCreate(
                ['vehiculo_id' => $vehicle->id],
                ['cantidad' => $quantity, 'ubicacion' => $location]
            );
        }
    }

    private function seedAppointments(User $client, array $vehicles): void
    {
        Cita::firstOrCreate(
            ['user_id' => $client->id, 'motivo' => 'DEMO | Visita para conocer el Corolla'],
            [
                'vehiculo_id' => $vehicles[0]->id,
                'fecha_hora' => now()->addDays(4)->setTime(10, 30),
                'estado' => 'pendiente',
            ]
        );
        Cita::firstOrCreate(
            ['user_id' => $client->id, 'motivo' => 'DEMO | Prueba de manejo de la CX-5'],
            [
                'vehiculo_id' => $vehicles[1]->id,
                'fecha_hora' => now()->addDays(7)->setTime(14, 0),
                'estado' => 'confirmada',
            ]
        );
    }

    private function seedInternationalOrders(User $user, string $pdfPath): void
    {
        $orders = [
            [
                'codigo' => 'DEMO-IMP-2026-001',
                'descripcion' => 'DEMO | Importación de SUV compacta',
                'proveedor' => 'Proveedor automotriz de demostración',
                'valor' => 28500,
                'progreso' => 62,
                'estado' => 'en_transito',
                'fecha_pedido' => now()->subMonths(2)->toDateString(),
                'fecha_estimada_llegada' => now()->subDays(3)->toDateString(),
            ],
            [
                'codigo' => 'DEMO-IMP-2026-002',
                'descripcion' => 'DEMO | Importación de sedán híbrido',
                'proveedor' => 'Distribuidor internacional demo',
                'valor' => 31700,
                'progreso' => 28,
                'estado' => 'en_espera',
                'fecha_pedido' => now()->subDays(18)->toDateString(),
                'fecha_estimada_llegada' => now()->addDays(35)->toDateString(),
            ],
        ];

        foreach ($orders as $data) {
            $order = PedidoInternacional::firstOrCreate(
                ['codigo' => $data['codigo']],
                ['user_id' => $user->id] + $data
            );

            ArchivoPedido::firstOrCreate(
                [
                    'pedido_internacional_id' => $order->id,
                    'nombre_original' => 'demo-soporte-importacion.pdf',
                ],
                ['ruta' => $pdfPath, 'tipo' => 'application/pdf']
            );
        }
    }

    private function seedFinancialMovements(User $user): void
    {
        $monthlyTypes = [
            ['ingreso', 'DEMO | Ingreso de venta'],
            ['gasto', 'DEMO | Gasto operativo'],
            ['impuesto', 'DEMO | Impuesto de importación'],
        ];

        for ($offset = 5; $offset >= 0; $offset--) {
            $month = now()->startOfMonth()->subMonths($offset);
            foreach ($monthlyTypes as $index => [$type, $description]) {
                $date = $offset === 0
                    ? now()->toDateString()
                    : $month->copy()->addDays(4 + $index * 7)->toDateString();
                TransaccionFinanciera::updateOrCreate(
                    ['descripcion' => $description.' - '.$month->format('Y-m')],
                    [
                        'user_id' => $user->id,
                        'tipo' => $type,
                        'monto' => [185000000, 24500000, 12700000][$index] + ($offset * 350000),
                        'moneda' => 'COP',
                        'vehiculo_id' => null,
                        'fecha' => $date,
                        'categoria' => 'DEMO | '.ucfirst(str_replace('_', ' ', $type)),
                    ]
                );
            }
        }

        foreach ([
            ['cuenta_por_cobrar', 'DEMO | Cuenta por cobrar - cliente de ejemplo', 42000000],
            ['cuenta_por_cobrar', 'DEMO | Anticipo pendiente - cliente de ejemplo', 18000000],
            ['cuenta_por_pagar', 'DEMO | Cuenta por pagar - proveedor de ejemplo', 29500000],
            ['cuenta_por_pagar', 'DEMO | Flete pendiente - transportadora de ejemplo', 12500000],
        ] as [$type, $description, $amount]) {
            TransaccionFinanciera::updateOrCreate(
                ['descripcion' => $description],
                [
                    'user_id' => $user->id,
                    'tipo' => $type,
                    'monto' => $amount,
                    'moneda' => 'COP',
                    'vehiculo_id' => null,
                    'fecha' => now()->subDays(10)->toDateString(),
                    'categoria' => 'DEMO | '.ucfirst(str_replace('_', ' ', $type)),
                ]
            );
        }

        $bmw = Vehiculo::query()
            ->where('marca', 'BMW')
            ->where('modelo', 'M4 Competition Demo')
            ->firstOrFail();

        TransaccionFinanciera::updateOrCreate(
            ['descripcion' => 'DEMO | Transporte internacional del BMW M4 Competition'],
            [
                'user_id' => $user->id,
                'tipo' => 'gasto',
                'monto' => 18500000,
                'moneda' => 'COP',
                'vehiculo_id' => $bmw->id,
                'fecha' => now()->toDateString(),
                'categoria' => 'Transporte internacional',
            ]
        );

        $sportage = Vehiculo::query()
            ->where('marca', 'Kia')
            ->where('modelo', 'Sportage Demo')
            ->firstOrFail();

        TransaccionFinanciera::updateOrCreate(
            ['descripcion' => 'DEMO | Venta de vehículo Kia Sportage'],
            [
                'user_id' => $user->id,
                'tipo' => 'ingreso',
                'monto' => 158000000,
                'moneda' => 'COP',
                'vehiculo_id' => $sportage->id,
                'fecha' => now()->toDateString(),
                'categoria' => 'Venta de vehículo',
            ]
        );

        TransaccionFinanciera::updateOrCreate(
            ['descripcion' => 'DEMO | Costo de venta Kia Sportage'],
            [
                'user_id' => $user->id,
                'tipo' => 'costo_venta',
                'monto' => 132000000,
                'moneda' => 'COP',
                'vehiculo_id' => $sportage->id,
                'fecha' => now()->toDateString(),
                'categoria' => 'Costo de venta',
            ]
        );
    }

    private function seedFinancialReport(User $user, string $pdfPath): void
    {
        InformeFinanciero::firstOrCreate(
            [
                'nombre_archivo' => 'demo-resumen-financiero.pdf',
                'tipo' => 'Resumen financiero - Demo',
                'periodo' => now()->format('Y-m'),
            ],
            [
                'user_id' => $user->id,
                'ruta' => $pdfPath,
            ]
        );
    }

    private function pdfDemostracion(): string
    {
        $stream = "BT\n/F1 18 Tf\n72 720 Td\n(DEMO - DOCUMENTO DE EJEMPLO) Tj\n0 -30 Td\n/F1 11 Tf\n(Archivo ficticio para presentacion; no es valido para tramites.) Tj\nET\n";
        $objects = [
            '<< /Type /Catalog /Pages 2 0 R >>',
            '<< /Type /Pages /Kids [3 0 R] /Count 1 >>',
            '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 612 792] /Resources << /Font << /F1 4 0 R >> >> /Contents 5 0 R >>',
            '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>',
            '<< /Length '.strlen($stream)." >>\nstream\n".$stream.'endstream',
        ];
        $pdf = "%PDF-1.4\n";
        $offsets = [0];

        foreach ($objects as $index => $object) {
            $offsets[] = strlen($pdf);
            $pdf .= ($index + 1)." 0 obj\n{$object}\nendobj\n";
        }

        $xrefOffset = strlen($pdf);
        $pdf .= 'xref'."\n0 ".(count($objects) + 1)."\n0000000000 65535 f \n";
        foreach (array_slice($offsets, 1) as $offset) {
            $pdf .= sprintf("%010d 00000 n \n", $offset);
        }

        return $pdf.'trailer << /Size '.(count($objects) + 1)." /Root 1 0 R >>\nstartxref\n{$xrefOffset}\n%%EOF";
    }
}
