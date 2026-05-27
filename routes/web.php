<?php

use App\Http\Controllers\Admin\VehiculoController as AdminVehiculoController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\VehiculoController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PedidoController;
use App\Http\Controllers\CitaController;
use App\Http\Controllers\FinanzaController;
use App\Models\User;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/catalogo', [VehiculoController::class, 'catalogo'])->name('catalogo');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'loginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'loginWeb']);
    Route::get('/register', [AuthController::class, 'registerForm'])->name('register');
    Route::post('/register', [AuthController::class, 'registerWeb']);
});

Route::get('/forgot-password', function () {
    return view('auth.forgot-password');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logoutWeb'])->name('logout');

    // Profile routes
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile/{user}', [ProfileController::class, 'update'])->name('profile.update');
    Route::get('/profile/change-password', [ProfileController::class, 'changePasswordForm'])->name('profile.changePassword');
    Route::post('/profile/change-password', [ProfileController::class, 'changePassword'])->name('profile.updatePassword');

    Route::get('/email/verify', function () {
        return view('auth.verify-email');
    })->name('verification.notice');

    Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
        $request->fulfill();

        return redirect('/catalogo')->with('verified', true);
    })->middleware(['signed'])->name('verification.verify');

    Route::post('/email/verification-notification', function (Request $request) {
        $request->user()->sendEmailVerificationNotification();

        return back()->with('status', 'verification-link-sent');
    })->middleware(['throttle:6,1'])->name('verification.send');
});

Route::middleware(['auth', 'role:jefe,contador'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::resource('vehiculos', AdminVehiculoController::class)->except(['show']);
        Route::get('dashboard/contador', [DashboardController::class, 'contador'])->name('dashboard.contador');

        // Rutas para generación de PDFs
        Route::get('finanzas/reporte-pdf', [FinanzaController::class, 'descargarReportePDF'])->name('finanzas.reporte.pdf');
        Route::get('pedidos/{id}/pdf', [PedidoController::class, 'descargarPedidoPDF'])->name('pedidos.pdf');
        Route::get('citas/{id}/pdf', [CitaController::class, 'descargarComprobantePDF'])->name('citas.pdf');
    });
