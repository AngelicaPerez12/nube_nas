<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EsAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        abort_unless($request->user() && $request->user()->is_admin, 403, 'Acceso exclusivo para administradores.');

        return $next($request);
    }
}