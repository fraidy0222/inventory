<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->user()) {
            return $next($request);
        }

        if ($request->user()->role === 'Admin' || $request->user()->role === 'Supervisor') {
            return redirect('/dashboard');
        } else {
            return redirect('/settings/profile');
        }

        return $next($request);
    }
}
