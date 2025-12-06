<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login');
        }

        $allowedRoles = array_map(fn($role) => UserRole::tryFrom($role), $roles);

        if (!in_array($user->role, $allowedRoles)) {
            abort(403, 'You do not have permission to access this page.');
        }

        return $next($request);
    }
}
