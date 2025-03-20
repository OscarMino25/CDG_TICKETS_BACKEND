<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth; // ✅ Importamos Auth
use App\Models\User; // ✅ Importamos el modelo User

class IsAdmin
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::guard('api')->user(); // ✅ Asegurar que obtenemos el usuario autenticado correctamente

        if ($user && $user->hasRole('admin')) { // ✅ Ahora esto funcionará correctamente
            return $next($request);
        } else {
            return response()->json(['message' => 'You are not ADMIN'], 403);
        }
    }
}
