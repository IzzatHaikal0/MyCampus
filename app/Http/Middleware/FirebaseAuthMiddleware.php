<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class FirebaseAuthMiddleware
{
    public function handle(Request $request, Closure $next, $role = null)
    {
        if (!session()->has('firebase_user')) {
            return redirect('/login')->with('error', 'Please login first.');
        }

        if ($role && session('firebase_user.role') !== $role) {
            return redirect('/dashboard')->with('error', 'Unauthorized access.');
        }

        return $next($request);
    }
}
