<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware to check if user has a specific role.
 * 
 * Usage in routes:
 * - Single role: 'role:admin'
 * - Multiple roles (any): 'role:admin,manager'
 */
class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @param string ...$roles
     * @return Response
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login');
        }

        // Unset the cached roles relation to force a fresh DB query.
        // This prevents stale Eloquent cache after registration.
        $user->unsetRelation('roles');

        $hasRole = false;
        foreach ($roles as $role) {
            if ($user->hasRole($role)) {
                $hasRole = true;
                break;
            }
        }

        if (!$hasRole) {
            abort(403, 'Unauthorized action. You do not have the required role.');
        }

        // Special case: unverified reseller trying to access reseller-only routes
        // should be redirected to customer dashboard — but NOT if they're already
        // accessing a customer route (which also lists 'reseller' as allowed).
        $isResellerOnlyRoute = $roles === ['reseller'];
        if ($isResellerOnlyRoute && $user->hasRole('reseller') && !$user->isVerifiedReseller()) {
            return redirect()->route('customer.dashboard')
                ->with('info', 'Akun reseller Anda belum diverifikasi. Anda akan diarahkan ke portal pelanggan.');
        }

        return $next($request);
    }
}
