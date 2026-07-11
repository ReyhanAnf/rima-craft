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
                'info'    => fn () => $request->session()->get('info'),
                'warning' => fn () => $request->session()->get('warning'),
            ],

            // Auth
            'auth' => [
                'user' => $user ? $user->only(['id', 'name', 'email', 'phone']) : null,
                'roles' => $user ? $user->roles->pluck('name')->toArray() : [],
                'reseller_status' => $user?->reseller_status,
                'permissions' => $user ? ($user->hasRole('dev-admin') ? \App\Models\Permission::pluck('name')->toArray() : \App\Models\Permission::whereHas('roles', function ($q) use ($user) {
                    $q->whereIn('role_id', $user->roles->pluck('id')->toArray());
                })->pluck('name')->toArray()) : [],
            ],

            // Global site config passed to all Vue pages
            'siteConfig' => [
                'business_name'    => config('settings.business_name', 'Rima Craft'),
                'business_phone'   => config('settings.business_phone', '6281234567890'),
                'hero_description' => config('settings.hero_description', ''),
                'logo_url'         => config('settings.logo_url'),
                'business_subtitle'=> config('settings.business_subtitle'),
                // New dynamic sponsors array — parsed from JSON, fallback to legacy 4-slot format
                'sponsors'         => (function () {
                    $json = config('settings.sponsors_json');
                    if ($json) {
                        $decoded = json_decode($json, true);
                        if (is_array($decoded)) return $decoded;
                    }
                    // Legacy fallback: read sponsor_1..4 keys
                    $legacy = [];
                    for ($i = 1; $i <= 4; $i++) {
                        $name = config("settings.sponsor_{$i}_name");
                        if ($name) {
                            $legacy[] = [
                                'name'        => $name,
                                'description' => '',
                                'link'        => '',
                                'logo_url'    => config("settings.sponsor_{$i}_logo_url", ''),
                            ];
                        }
                    }
                    return $legacy;
                })(),
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
