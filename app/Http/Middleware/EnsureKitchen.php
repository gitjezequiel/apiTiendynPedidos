<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureKitchen
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check() || auth()->user()->role !== 'kitchen') {
            return redirect()->route('kitchen.login')
                ->withErrors(['email' => 'Acceso exclusivo para usuarios de cocina.']);
        }

        return $next($request);
    }
}
