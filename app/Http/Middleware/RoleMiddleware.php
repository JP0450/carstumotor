<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\User;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string $role, string ...$roles): Response
    {
        $user = auth()->user();
        $requiredRoles = array_merge([$role], $roles);
        $allowedRoles = [];

        foreach ($requiredRoles as $roleSegment) {
            foreach (explode(',', $roleSegment) as $item) {
                $item = trim($item);
                if ($item !== '') {
                    $allowedRoles[] = $item;
                }
            }
        }

        \Log::info('RoleMiddleware check', [
            'user_exists' => $user ? true : false,
            'user_id' => $user?->id,
            'user_email' => $user?->email,
            'user_role' => $user?->role,
            'required_roles' => $allowedRoles,
            'request_path' => $request->path(),
        ]);

        if (!$user) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json(['message' => 'No autenticado.'], 401);
            }

            return redirect()->guest(route('login'));
        }

        // Si el usuario es `jefe`, le concedemos acceso global (super-rol).
        if ($user->hasRole(User::ROLE_JEFE)) {
            return $next($request);
        }

        $hasRole = false;
        foreach ($allowedRoles as $role) {
            if ($user->hasRole(trim($role))) {
                $hasRole = true;
                break;
            }
        }

        \Log::info('RoleMiddleware result', [
            'has_role' => $hasRole,
            'allowed_roles' => $allowedRoles,
        ]);

        if (!$hasRole) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'message' => 'No tiene permisos para acceder a este recurso.',
                ], 403);
            }

            return redirect()->route('catalogo')->with('error', 'No tenés permiso para acceder a esa sección.');
        }

        return $next($request);
    }
}
