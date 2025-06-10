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
        if (!Auth::check()) {
            return redirect('/login');
        }

        $user = Auth::user();

        if ($user->role !== 'admin') {
            return redirect('/')
                ->with('error', "Sorry {$user->name}, you don't have admin privileges. Please contact the system administrator if you need access.");
        }

        return $next($request);
    }
}
