<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (!$request->user()) {
            return redirect()->route('login');
        }

        $user = $request->user();
        $userRole = $user->role;
        
        // Map role aliases to actual role names
        $roleMap = [
            'gl' => 'group_leader',
            'spv' => 'supervisor',
            'admin' => 'admin',
            'fuelman' => 'fuelman',
        ];
        
        // Convert requested roles using the map
        $allowedRoles = array_map(function($role) use ($roleMap) {
            return $roleMap[$role] ?? $role;
        }, $roles);
        
        // Check if user's role is in allowed roles
        if (!in_array($userRole, $allowedRoles)) {
            abort(403, 'Anda tidak memiliki hak akses untuk halaman ini.');
        }

        return $next($request);
    }
}
