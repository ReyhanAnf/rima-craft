<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware to check if user has a specific permission.
 * 
 * Usage in routes:
 * - Single permission: 'permission:create-sale'
 * - Multiple permissions (any): 'permission:create-sale,edit-sale'
 */
class PermissionMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @param string ...$permissions
     * @return Response
     */
    public function handle(Request $request, Closure $next, string ...$permissions): Response
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login');
        }

        $hasPermission = false;
        foreach ($permissions as $permission) {
            if (Gate::allows($permission)) {
                $hasPermission = true;
                break;
            }
        }

        if (!$hasPermission) {
            abort(403, 'Unauthorized action. You do not have the required permission.');
        }

        return $next($request);
    }
}
