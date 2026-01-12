<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPagePermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        if (!$user) {
            return redirect()->route('login');
        }

        // Admin can access all pages
        if ($user->isAdmin()) {
            return $next($request);
        }

        $routeName = $request->route()->getName();

        // Check if user can access this page
        if (!$user->canAccessPage($routeName)) {
            abort(403, 'You do not have permission to access this page.');
        }

        return $next($request);
    }
}

