<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * Root Blade template for Inertia.
     */
    protected $rootView = 'app';

    /**
     * Asset versioning for cache busting.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Shared data available to all Inertia pages.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();

        return [
            ...parent::share($request),

            // Flash messages
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error'   => fn () => $request->session()->get('error'),
            ],

            // Auth
            'auth' => [
                'user' => $user ? $user->only(['id', 'name', 'email', 'phone']) : null,
                'roles' => $user ? $user->roles->pluck('name')->toArray() : [],
                'permissions' => $user ? \App\Models\Permission::whereHas('roles', function ($q) use ($user) {
                    $q->whereIn('role_id', $user->roles->pluck('id')->toArray());
                })->pluck('name')->toArray() : [],
            ],

            // Global site config passed to all Vue pages
            'siteConfig' => [
                'business_name'    => config('settings.business_name', 'Rima Craft'),
                'business_phone'   => config('settings.business_phone', '6281234567890'),
                'hero_description' => config('settings.hero_description', ''),
                'checkout_url'     => route('order.checkout'),
                'order_store_url'  => route('order.store'),
                'catalog_url'      => route('catalog.index'),
                'login_url'        => route('login'),
                'terms_url'        => route('page.terms'),
                'privacy_url'      => route('page.privacy'),
                'shipping_url'     => route('page.shipping'),
            ],

            // Sidebar Menu configuration
            'menus' => config('menus.categories', []),
        ];
    }
}
