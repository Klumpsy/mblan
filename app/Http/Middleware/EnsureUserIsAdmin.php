<?php

namespace App\Http\Middleware;

use App\Facades\Flash;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $userName = auth()->user()->name;

        if (auth()->check() && auth()->user()->role !== 'admin') {

            return redirect('/')
                ->with('error', "Sorry {$userName}, you don't have admin privileges. Please contact the system administrator if you need access.");
        }

        return $next($request);
    }
}
