<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CitaController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FinanzaController;
use App\Http\Controllers\InventarioController;
use App\Http\Controllers\PedidoController;
use App\Http\Controllers\VehiculoController;
use App\Models\User;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    // Usuario Externo
    Route::middleware('role:' . User::ROLE_EXTERNO)->group(function () {
        Route::get('/vehiculos', [VehiculoController::class, 'index']);
        Route::get('/vehiculos/buscar', [VehiculoController::class, 'search']);
        Route::get('/vehiculos/{id}', [VehiculoController::class, 'show']);

        Route::post('/citas', [CitaController::class, 'store']);
        Route::get('/citas', [CitaController::class, 'index']);
        Route::patch('/citas/{id}/cancelar', [CitaController::class, 'cancelar']);
    });

    // KPIs (utilidades netas, impuestos, márgenes): jefe y contador / finanzas
    Route::middleware('role:' . User::ROLE_JEFE . ',' . User::ROLE_CONTADOR)->group(function () {
        Route::get('/dashboard/ejecutivo', [DashboardController::class, 'ejecutivo']);
    });

    // Profesional negocios internacionales: logística y seguimiento interno (pedidos, archivos).
    // No incluye CRUD del catálogo público de vehículos que consume el cliente (externo).
    Route::middleware('role:' . User::ROLE_NEGOCIOS)->group(function () {
        Route::post('/pedidos', [PedidoController::class, 'store']);
        Route::get('/pedidos', [PedidoController::class, 'index']);
        Route::patch('/pedidos/{id}/progreso', [PedidoController::class, 'updateProgress']);
        Route::post('/pedidos/{id}/archivos', [PedidoController::class, 'uploadArchivo']);
        Route::get('/pedidos/{id}/archivos', [PedidoController::class, 'archivos']);
    });

    // Contador / finanzas: transacciones, informes tipo NIIF / DIAN (vía informes y estados financieros)
    Route::middleware('role:' . User::ROLE_CONTADOR)->group(function () {
        Route::post('/finanzas/transacciones', [FinanzaController::class, 'storeTransaccion']);
        Route::get('/finanzas/informes', [FinanzaController::class, 'listarInformes']);
        Route::post('/finanzas/informes', [FinanzaController::class, 'subirInforme']);
        Route::get('/finanzas/flujo-caja', [FinanzaController::class, 'flujoCaja']);
        Route::get('/finanzas/estado-resultados', [FinanzaController::class, 'estadoResultados']);
        Route::get('/finanzas/cuentas-por-pagar', [FinanzaController::class, 'cuentasPorPagar']);
        Route::get('/finanzas/cuentas-por-cobrar', [FinanzaController::class, 'cuentasPorCobrar']);
    });

    // Inventario físico / stock: jefe y contador
    Route::middleware('role:' . User::ROLE_JEFE . ',' . User::ROLE_CONTADOR)->group(function () {
        Route::get('/inventario', [InventarioController::class, 'index']);
        Route::post('/inventario', [InventarioController::class, 'store']);
        Route::put('/inventario/{id}', [InventarioController::class, 'updateStock']);
        Route::post('/inventario/salida', [InventarioController::class, 'salida']);
        Route::get('/inventario/alertas', [InventarioController::class, 'alertas']);
    });

    // Catálogo público (autos que ve el cliente / externo): alta y edición solo jefe y contador
    Route::middleware('role:' . User::ROLE_JEFE . ',' . User::ROLE_CONTADOR)->group(function () {
        Route::post('/vehiculos', [VehiculoController::class, 'store']);
        Route::put('/vehiculos/{id}', [VehiculoController::class, 'update']);
        Route::delete('/vehiculos/{id}', [VehiculoController::class, 'destroy']);
    });

    // Citas pendientes (acceso jefe / admin)
    Route::middleware('role:' . User::ROLE_JEFE)->group(function () {
        Route::get('/citas/pendientes', [CitaController::class, 'pendientes']);
    });
});
