<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (! $request->user()) {
            abort(403, 'Unauthorized action.');
        }

        $userRole = $request->user()->role;

        // Check if user has ANY of the required roles
        if (in_array($userRole, $roles)) {
            return $next($request);
        }

        abort(403, 'Unauthorized action.');
    }
}
