<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleAccess
{
    /**
     * Permite acceso solo a los tipos de rol indicados.
     *
     * Uso en rutas: ->middleware('role:1,3')
     */
    public function handle(Request $request, Closure $next, string ...$allowedRoles): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Debes iniciar sesión');
        }

        $user = Auth::user();
        $roleType = $user->role?->type;

        if (is_null($roleType)) {
            abort(403, 'No tienes un rol asignado para acceder a esta sección.');
        }

        $allowedRoles = array_map('strval', $allowedRoles);

        if (!in_array((string) $roleType, $allowedRoles, true)) {
            abort(403, 'No tienes permiso para acceder a esta sección.');
        }

        return $next($request);
    }
}
