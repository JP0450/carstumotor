<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function __construct(
        private AuthService $authService
    ) {}

    public function register(RegisterRequest $request): JsonResponse
    {
        $user = $this->authService->register($request->validated());
        $this->authService->loginUser($user);

        return response()->json([
            'user' => $user,
            'message' => 'Usuario registrado y sesión iniciada.',
        ], 201);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $user = $this->authService->authenticate(
            $request->input('email'),
            $request->input('password')
        );

        if (!$user) {
            return response()->json([
                'message' => 'Credenciales incorrectas.',
            ], 401);
        }

        $this->authService->loginUser($user);

        return response()->json([
            'user' => $user,
            'message' => 'Sesión iniciada correctamente.',
        ]);
    }

    public function logout(): JsonResponse
    {
        $this->authService->logoutUser();

        return response()->json([
            'message' => 'Sesión cerrada correctamente.',
        ]);
    }

    public function me(): JsonResponse
    {
        return response()->json(auth()->user());
    }

    public function loginForm(): View
    {
        return view('auth.login');
    }

    public function registerForm(): View
    {
        return view('auth.register');
    }

    public function loginWeb(LoginRequest $request): RedirectResponse
    {
        $user = $this->authService->authenticate(
            $request->input('email'),
            $request->input('password')
        );

        if (!$user) {
            return back()->withErrors(['email' => 'Credenciales incorrectas.'])->withInput();
        }

        $this->authService->loginUser($user);

        return redirect()->intended('/catalogo');
    }

    public function registerWeb(RegisterRequest $request): RedirectResponse
    {
        $user = $this->authService->register($request->validated());
        $this->authService->loginUser($user);
        $user->sendEmailVerificationNotification();

        return redirect('/email/verify');
    }

    public function logoutWeb(Request $request): RedirectResponse
    {
        $this->authService->logoutUser();

        return redirect('/');
    }
}
