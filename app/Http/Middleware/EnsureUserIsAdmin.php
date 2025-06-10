<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $userName = Auth::user()->name;

        if (Auth::check() && Auth::user()->role !== 'admin') {

            return redirect('/')
                ->with('error', "Sorry {$userName}, you don't have admin privileges. Please contact the system administrator if you need access.");
        }

        return $next($request);
    }
}
