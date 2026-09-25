<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EsAdministrador
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
   public function handle(Request $request, Closure $next): Response
{
    if ($request->user() && $request->user()->rol === 'administrador') {
        return $next($request);
    }

    abort(403, 'No tienes permiso para acceder aquí.');
}
}
