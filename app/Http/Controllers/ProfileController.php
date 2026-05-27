<?php

namespace App\Http\Controllers;

use App\Http\Requests\ChangePasswordRequest;
use App\Http\Requests\UpdateProfileRequest;
use App\Services\UserProfileService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function __construct(
        private UserProfileService $userProfileService
    ) {}

    /**
     * Mostrar el perfil del usuario autenticado.
     */
    public function show(): View
    {
        $user = $this->userProfileService->getProfile(auth()->id());

        return view('profile.show', compact('user'));
    }

    /**
     * Mostrar formulario para editar el perfil.
     */
    public function edit(): View
    {
        $user = $this->userProfileService->getProfile(auth()->id());

        return view('profile.edit', compact('user'));
    }

    /**
     * Actualizar el perfil del usuario.
     */
    public function update(UpdateProfileRequest $request): RedirectResponse
    {
        $updated = $this->userProfileService->updateProfile(
            auth()->id(),
            $request->validated()
        );

        if (!$updated) {
            return back()->with('error', 'No se pudo actualizar el perfil.');
        }

        return redirect()->route('profile.show')
            ->with('success', 'Perfil actualizado correctamente.');
    }

    /**
     * Mostrar formulario para cambiar contraseña.
     */
    public function changePasswordForm(): View
    {
        return view('profile.change-password');
    }

    /**
     * Cambiar la contraseña del usuario.
     */
    public function changePassword(ChangePasswordRequest $request): RedirectResponse
    {
        $changed = $this->userProfileService->changePassword(
            auth()->id(),
            $request->input('current_password'),
            $request->input('new_password')
        );

        if (!$changed) {
            return back()->with('error', 'La contraseña actual es incorrecta.');
        }

        return redirect()->route('profile.show')
            ->with('success', 'Contraseña cambiada correctamente.');
    }
}
