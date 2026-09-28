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

Route::middleware('guest')->group(function () {
    Route::get('/forgot-password', function () {
        return view('auth.forgot-password');
    })->name('password.request');

    Route::post('/forgot-password', function (Illuminate\Http\Request $request) {
        $request->validate(['email' => 'required|email']);
        try {
            $status = Illuminate\Support\Facades\Password::sendResetLink($request->only('email'));
        } catch (\Throwable $e) {
            \Log::warning('No se pudo enviar reset: '.$e->getMessage(), ['email' => $request->email]);
            return back()->with('status', 'Si tu email existe en nuestra base, recibirás un enlace. Revisa también spam.');
        }
        return $status === Illuminate\Support\Facades\Password::RESET_LINK_SENT
            ? back()->with('status', __($status))
            : back()->withErrors(['email' => __($status)]);
    })->name('password.email');

    Route::get('/reset-password/{token}', function (string $token) {
        return view('auth.reset-password', ['token' => $token, 'email' => request('email')]);
    })->name('password.reset');

    Route::post('/reset-password', function (Illuminate\Http\Request $request) {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => ['required', 'string', 'min:8', 'confirmed', Illuminate\Validation\Rules\Password::min(8)->letters()->numbers()->symbols()],
        ]);
        try {
            $status = Illuminate\Support\Facades\Password::reset(
                $request->only('email', 'password', 'password_confirmation', 'token'),
                function ($user, $password) {
                    $user->forceFill(['password' => Illuminate\Support\Facades\Hash::make($password)])->setRememberToken(Illuminate\Support\Str::random(60));
                    $user->save();
                    event(new Illuminate\Auth\Events\PasswordReset($user));
                }
            );
        } catch (\Throwable $e) {
            \Log::error('Error al resetear password: '.$e->getMessage());
            return back()->withErrors(['email' => 'No se pudo restablecer. Intenta de nuevo.']);
        }
        return $status === Illuminate\Support\Facades\Password::PASSWORD_RESET
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

    Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect('/catalogo')->with('verified', true);
        }
        try {
            $request->fulfill();
        } catch (\Throwable $e) {
            \Log::warning('Error al verificar email: '.$e->getMessage(), ['user_id' => $request->user()->id ?? null]);
            return redirect()->route('verification.notice')->withErrors(['email' => 'Enlace inválido o expirado. Solicita uno nuevo.']);
        }
        return redirect('/catalogo')->with('verified', true);
    })->middleware(['signed', 'throttle:6,1'])->name('verification.verify');

    Route::post('/email/verification-notification', function (Request $request) {
        try {
            $request->user()->sendEmailVerificationNotification();
        } catch (\Throwable $e) {
            \Log::warning('No se pudo reenviar verificación: '.$e->getMessage(), ['user_id' => $request->user()->id]);
            return back()->with('status', 'verification-link-sent')->with('warning', 'Si el correo no llega, verifica la configuración SMTP.');
        }

        return back()->with('status', 'verification-link-sent');
    })->middleware(['throttle:6,1'])->name('verification.send');

    // Si alguien entra por GET a /email/verification-notification (ej. recarga), redirigir a /email/verify sin 405/500
    Route::get('/email/verification-notification', function () {
        return redirect()->route('verification.notice');
    })->middleware(['throttle:6,1']);
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
