<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            \App\Http\Middleware\HandleInertiaRequests::class,
        ]);
        $middleware->alias([
            'role' => \App\Http\Middleware\RoleMiddleware::class,
            'permission' => \App\Http\Middleware\PermissionMiddleware::class,
        ]);
        $middleware->redirectUsersTo(function (Request $request) {
            $user = $request->user();
            if ($user) {
                if ($user->roles()->where('name', 'reseller')->exists()) {
                    return route('reseller.dashboard');
                }
                if ($user->roles()->where('name', 'customer')->exists()) {
                    return route('customer.dashboard');
                }
            }
            return route('dashboard');
        });
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->wantsJson(),
        );

        $exceptions->respond(function (Response $response, \Throwable $exception, Request $request) {
            if (! app()->environment('local') && in_array($response->getStatusCode(), [500, 503, 404, 403, 401, 400])) {
                return Inertia::render('Error', [
                    'status'  => $response->getStatusCode(),
                    'message' => $exception->getMessage() ?: null,
                ])
                    ->toResponse($request)
                    ->setStatusCode($response->getStatusCode());
            } elseif ($response->getStatusCode() === 404 && $request->header('X-Inertia')) {
                return Inertia::render('Error', ['status' => 404])
                    ->toResponse($request)
                    ->setStatusCode(404);
            }

            return $response;
        });
    })->create();
