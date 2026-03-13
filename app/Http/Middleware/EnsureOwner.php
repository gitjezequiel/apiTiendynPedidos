<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureOwner
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check() || auth()->user()->role !== 'owner') {
            return redirect()->route('admin.login')
                ->withErrors(['email' => 'Acceso exclusivo para dueños de restaurante.']);
        }

        return $next($request);
    }
}
