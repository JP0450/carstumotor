<?php

use App\Http\Controllers\Admin\VehiculoController as AdminVehiculoController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CitaCompraController;
use App\Http\Controllers\CitaController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FinanzaController;
use App\Http\Controllers\FinanzaWebController;
use App\Http\Controllers\PedidoController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\VehiculoController;
use App\Http\Controllers\VehiculoInternacionalController;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/catalogo', [VehiculoController::class, 'catalogo'])->name('catalogo');
Route::get('/catalogo/{id}', [VehiculoController::class, 'ficha'])->name('catalogo.show');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'loginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'loginWeb']);
    Route::get('/register', [AuthController::class, 'registerForm'])->name('register');
    Route::post('/register', [AuthController::class, 'registerWeb']);
});

Route::middleware('guest')->group(function () {
    Route::get('/forgot-password', function () {
        return view('auth.forgot-password');
    })->name('password.request');

    Route::post('/forgot-password', function (Request $request) {
        $request->validate(['email' => 'required|email']);
        try {
            $status = Password::sendResetLink($request->only('email'));
        } catch (Throwable $e) {
            Log::warning('No se pudo enviar reset: '.$e->getMessage(), ['email' => $request->email]);

            return back()->with('status', 'Si tu email existe en nuestra base, recibirás un enlace. Revisa también spam.');
        }

        return $status === Password::RESET_LINK_SENT
            ? back()->with('status', __($status))
            : back()->withErrors(['email' => __($status)]);
    })->name('password.email');

    Route::get('/reset-password/{token}', function (string $token) {
        return view('auth.reset-password', ['token' => $token, 'email' => request('email')]);
    })->name('password.reset');

    Route::post('/reset-password', function (Request $request) {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => ['required', 'string', 'min:8', 'confirmed', Illuminate\Validation\Rules\Password::min(8)->letters()->numbers()->symbols()],
        ]);
        try {
            $status = Password::reset(
                $request->only('email', 'password', 'password_confirmation', 'token'),
                function ($user, $password) {
                    $user->forceFill(['password' => Hash::make($password)])->setRememberToken(Str::random(60));
                    $user->save();
                    event(new PasswordReset($user));
                }
            );
        } catch (Throwable $e) {
            Log::error('Error al resetear password: '.$e->getMessage());

            return back()->withErrors(['email' => 'No se pudo restablecer. Intenta de nuevo.']);
        }

        return $status === Password::PASSWORD_RESET
            ? redirect()->route('login')->with('status', __($status))
            : back()->withErrors(['email' => [__($status)]]);
    })->name('password.update');
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

    Route::get('/email/verify/{id}/{hash}', function (Request $request, string $id, string $hash) {
        $user = $request->user();
        if (! $user || (string) $user->getKey() !== (string) $id) {
            return redirect()->route('verification.notice')->withErrors(['email' => 'Enlace inválido.']);
        }
        if ($user->hasVerifiedEmail()) {
            return redirect('/catalogo')->with('verified', true);
        }
        if (! hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
            return redirect()->route('verification.notice')->withErrors(['email' => 'Enlace inválido o expirado. Solicita uno nuevo.']);
        }
        // Verificar firma manualmente ya lo hace middleware signed, pero si falla será 403 automático
        try {
            $user->markEmailAsVerified();
            event(new Verified($user));
        } catch (Throwable $e) {
            Log::warning('Error al verificar email: '.$e->getMessage(), ['user_id' => $user->id ?? null]);

            return redirect()->route('verification.notice')->withErrors(['email' => 'No se pudo verificar. Solicita uno nuevo.']);
        }

        return redirect('/catalogo')->with('verified', true);
    })->middleware(['signed', 'throttle:6,1'])->name('verification.verify');

    Route::post('/email/verification-notification', function (Request $request) {
        try {
            $request->user()->sendEmailVerificationNotification();
        } catch (Throwable $e) {
            Log::warning('No se pudo reenviar verificación: '.$e->getMessage(), ['user_id' => $request->user()->id]);

            return back()->with('status', 'verification-link-sent')->with('warning', 'Si el correo no llega, verifica la configuración SMTP.');
        }

        return back()->with('status', 'verification-link-sent');
    })->middleware(['throttle:6,1'])->name('verification.send');

    // Si alguien entra por GET a /email/verification-notification (ej. recarga), redirigir a /email/verify sin 405/500
    Route::get('/email/verification-notification', function () {
        return redirect()->route('verification.notice');
    })->middleware(['throttle:6,1']);

    Route::middleware('role:externo')->group(function () {
        Route::get('/catalogo/{vehiculo}/agendar-compra', [CitaCompraController::class, 'create'])
            ->name('catalogo.compra.create');
        Route::post('/catalogo/{vehiculo}/agendar-compra', [CitaCompraController::class, 'store'])
            ->name('catalogo.compra.store');
        Route::get('/mis-solicitudes-compra', [CitaCompraController::class, 'mine'])
            ->name('compras.mine');
        Route::put('/mis-solicitudes-compra/{cita}/horario', [CitaCompraController::class, 'updateSchedule'])
            ->name('compras.schedule.update');
        Route::post('/mis-solicitudes-compra/{cita}/confirmar-asistencia', [CitaCompraController::class, 'confirmAttendance'])
            ->name('compras.attendance.confirm');
    });

    Route::middleware('role:jefe,contador')->group(function () {
        Route::get('/admin/solicitudes-compra', [CitaCompraController::class, 'index'])
            ->name('admin.compras.index');
        Route::put('/admin/solicitudes-compra/{cita}/estado', [CitaCompraController::class, 'updateStatus'])
            ->name('admin.compras.status');
    });

    Route::middleware('role:jefe,contador')->group(function () {
        Route::get('/admin/dashboard/gerencial', [DashboardController::class, 'gerencial'])
            ->name('admin.dashboard.gerencial');
        Route::get('/admin/dashboard/gerencial/reporte.pdf', [DashboardController::class, 'exportarPdf'])
            ->name('admin.dashboard.gerencial.pdf');
        Route::get('/admin/dashboard/gerencial/reporte.csv', [DashboardController::class, 'exportarCsv'])
            ->name('admin.dashboard.gerencial.csv');
        Route::redirect('/admin/dashboard/contador', '/admin/dashboard/gerencial')
            ->name('admin.dashboard.contador');
        Route::get('/finanzas', [FinanzaWebController::class, 'index'])->name('finanzas.index');
        Route::get('/finanzas/{transaccion}/editar', [FinanzaWebController::class, 'edit'])->name('finanzas.edit');
        Route::post('/finanzas', [FinanzaWebController::class, 'store'])->name('finanzas.store');
        Route::put('/finanzas/{transaccion}', [FinanzaWebController::class, 'update'])->name('finanzas.update');
        Route::delete('/finanzas/{transaccion}', [FinanzaWebController::class, 'destroy'])->name('finanzas.destroy');
    });

    Route::middleware('role:jefe,negocios_internacionales')
        ->prefix('internacional/vehiculos')
        ->name('internacional.vehiculos.')
        ->controller(VehiculoInternacionalController::class)
        ->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/{vehiculo}', 'edit')->name('edit');
            Route::put('/{vehiculo}', 'update')->name('update');
            Route::post('/{vehiculo}/documentos', 'storeDocument')->name('documentos.store');
            Route::get('/{vehiculo}/documentos/{documento}', 'showDocument')->name('documentos.show');
            Route::get('/{vehiculo}/documentos/{documento}/descargar', 'downloadDocument')->name('documentos.download');
            Route::delete('/{vehiculo}/documentos/{documento}', 'destroyDocument')->name('documentos.destroy');
        });
});

Route::middleware(['auth', 'role:jefe,contador'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::post('vehiculos/{vehiculo}/venta', [AdminVehiculoController::class, 'registrarVenta'])
            ->name('vehiculos.venta');
        Route::resource('vehiculos', AdminVehiculoController::class)->except(['show']);
        // Rutas para generación de PDFs
        Route::get('finanzas/reporte-pdf', [FinanzaController::class, 'descargarReportePDF'])->name('finanzas.reporte.pdf');
        Route::get('pedidos/{id}/pdf', [PedidoController::class, 'descargarPedidoPDF'])->name('pedidos.pdf');
        Route::get('citas/{id}/pdf', [CitaController::class, 'descargarComprobantePDF'])->name('citas.pdf');
    });
