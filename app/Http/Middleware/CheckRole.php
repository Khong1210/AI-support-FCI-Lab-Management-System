<?php

namespace App\Http\Middleware;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }
        $user = Auth::user();

        if ((int)$user->user_role === 1) {
            return $next($request);
        }
        $cleanRoles = array_map('trim', $roles);

        $userRole = (string) $user->user_role;

        if (!in_array($userRole, $cleanRoles)) {
            abort(403, 'Unauthorized access.');
        }
        return $next($request);
    }
}