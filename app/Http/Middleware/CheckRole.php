<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request payload.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        // 1. Enforce session authentication validation checks
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        // 2. Grant superuser permission bypass specifically using the 'user_role' database property
        if ((int)$user->user_role === 1) {
            return $next($request);
        }

        // 3. Normalize structural routing parameter items by eliminating hidden white space characters
        $cleanRoles = array_map('trim', $roles);

        // 4. Map the explicit account structural profile configuration attribute to string format
        $userRole = (string) $user->user_role;

        // 5. Evaluate authorization parameter clearance matching conditions
        if (!in_array($userRole, $cleanRoles)) {
            abort(403, 'Unauthorized action.');
        }

        return $next($request);
    }
}