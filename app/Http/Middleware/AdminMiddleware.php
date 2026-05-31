<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        if (!$user || !$user->hasAdminAccess()) {
            return redirect('/user-dashboard')->with('error', 'Access denied. Admins only.');
        }

        return $next($request);
    }
}
