<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\CatalogController;
use App\Http\Controllers\MyOrderController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\Portal\CustomerPortalController;
use App\Http\Controllers\Portal\PartnerPortalController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::controller(CatalogController::class)->group(function () {
    Route::get('/', 'index')->name('catalog.index');
    Route::get('/katalog', 'shop')->name('catalog.shop');
    Route::get('/katalog/filter', 'filter')->name('catalog.filter');
});

Route::name('page.')->group(function () {
    Route::get('/syarat-ketentuan', function () {
        return Inertia::render('StaticPage', [
            'title' => 'Syarat & Ketentuan',
            'content' => config('settings.page_terms'),
        ]);
    })->name('terms');

    Route::get('/kebijakan-privasi', function () {
        return Inertia::render('StaticPage', [
            'title' => 'Kebijakan Privasi',
            'content' => config('settings.page_privacy'),
        ]);
    })->name('privacy');

    Route::get('/pengiriman-retur', function () {
        return Inertia::render('StaticPage', [
            'title' => 'Pengiriman & Retur',
            'content' => config('settings.page_shipping'),
        ]);
    })->name('shipping');
});

Route::prefix('order')
    ->name('order.')
    ->controller(OrderController::class)
    ->group(function () {
        Route::get('/checkout', 'checkout')->name('checkout');
        Route::post('/store', 'store')->name('store');
        Route::get('/success/{order}', 'success')->name('success');
    });
// Login & Register pages accessible by anyone (even logged-in users switching portals)
Route::controller(AuthController::class)->group(function () {
    // Redirect default login to customer login
    Route::get('login', function () {
        return redirect()->route('customer.login');
    })->name('login');

    // Customer login & register pages
    Route::get('customer/login', 'createCustomerLogin')->name('customer.login');
    Route::get('customer/register', 'showCustomerRegistration')->name('customer.register');

    // Reseller login & register pages
    Route::get('reseller/login', 'createResellerLogin')->name('reseller.login');
    Route::get('reseller/register', 'showResellerRegistration')->name('reseller.register');

    // Parameterized fallback register page
    Route::get('register/{type}', 'showRegistration')->name('register.show');
});

// POST submit actions — only for guests (prevent re-login if already authenticated)
Route::middleware('guest')->controller(AuthController::class)->group(function () {
    Route::post('login', 'store')->name('login.store'); // backward compatibility fallback

    // Customer submit
    Route::post('customer/login', 'storeCustomerLogin')->name('customer.login.store');
    Route::post('customer/register', 'registerCustomer')->name('customer.register.submit');

    // Reseller submit
    Route::post('reseller/login', 'storeResellerLogin')->name('reseller.login.store');
    Route::post('reseller/register', 'registerReseller')->name('reseller.register.submit');

    // Parameterized fallback register submit
    Route::post('register/{type}', 'register')->name('register.submit');
});

Route::middleware('auth')->group(function () {
    Route::post('logout', [AuthController::class, 'destroy'])->name('logout');

    // Redirect /my-orders ke portal pesanan sesuai peran
    Route::get('my-orders', function () {
        $user = auth()->user();
        if ($user->roles()->where('name', 'reseller')->exists()) {
            return redirect()->route('reseller.orders');
        }
        return redirect()->route('customer.orders');
    })->name('my-orders.index');

    // Redirect /my-orders/{orderNumber} ke portal show order
    Route::get('my-orders/{orderNumber}', function ($orderNumber) {
        return redirect()->route('customer.orders');
    })->name('my-orders.show');


    Route::middleware(['role:customer', 'permission:view-my-profile'])
        ->prefix('customer')
        ->name('customer.')
        ->controller(CustomerPortalController::class)
        ->group(function () {
            Route::get('/dashboard', 'dashboard')->name('dashboard');
            Route::get('/orders', 'orders')->name('orders');
            Route::get('/profile', 'profile')->name('profile');
            Route::post('/profile/update', 'updateProfile')->name('profile.update');
        });

    Route::middleware(['role:reseller', 'permission:view-my-profile'])
        ->prefix('reseller')
        ->name('reseller.')
        ->controller(\App\Http\Controllers\Portal\ResellerPortalController::class)
        ->group(function () {
            Route::get('/dashboard', 'dashboard')->name('dashboard');
            Route::get('/orders', 'orders')->name('orders');
            Route::get('/billing', 'billing')->name('billing');
            Route::get('/profile', 'profile')->name('profile');
            Route::post('/profile/update', 'updateProfile')->name('profile.update');
        });
});
