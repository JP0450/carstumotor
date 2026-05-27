<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsExterno
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user || !$user->hasRole(User::ROLE_EXTERNO)) {
            return redirect('/')->with(
                'catalogo_denied',
                'El catálogo interactivo está disponible solo para cuentas de cliente.'
            );
        }

        return $next($request);
    }
}
