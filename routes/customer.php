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

Route::prefix('api/regions')
    ->name('api.regions.')
    ->controller(\App\Http\Controllers\RegionController::class)
    ->group(function () {
        Route::get('/{province}/cities', 'getCities')->name('cities');
        Route::post('/calculate', 'calculateTotals')->name('calculate');
    });
// Google OAuth
Route::prefix('auth/google')
    ->name('auth.google.')
    ->controller(\App\Http\Controllers\Auth\GoogleAuthController::class)
    ->group(function () {
        Route::get('/redirect', 'redirect')->name('redirect');
        Route::get('/callback', 'callback')->name('callback');
        Route::get('/complete', 'showComplete')->name('complete');
        Route::post('/complete', 'storeComplete')->name('complete.store');
    });

// Password Reset
Route::middleware('guest')->group(function () {
    Route::get('forgot-password', [\App\Http\Controllers\Auth\ForgotPasswordController::class, 'show'])
        ->name('password.request');
    Route::post('forgot-password', [\App\Http\Controllers\Auth\ForgotPasswordController::class, 'sendResetLink'])
        ->name('password.email');
    Route::get('reset-password/{token}', [\App\Http\Controllers\Auth\ResetPasswordController::class, 'show'])
        ->name('password.reset');
    Route::post('reset-password', [\App\Http\Controllers\Auth\ResetPasswordController::class, 'reset'])
        ->name('password.update');
});

// Login & Register pages accessible by anyone (even logged-in users switching portals)
Route::controller(AuthController::class)->group(function () {
    // Single unified login page for all public roles
    Route::get('login', 'createLogin')->name('login');

    // Single unified register page (customer default, toggle to reseller)
    Route::get('register', 'showRegistration')->name('register.show');

    // Backward compatibility redirects
    Route::get('customer/login', fn() => redirect()->route('login'));
    Route::get('customer/register', fn() => redirect()->route('register.show'));
    Route::get('reseller/login', fn() => redirect()->route('login'));
    Route::get('reseller/register', fn() => redirect()->route('register.show', ['type' => 'reseller']));
    Route::get('register/{type}', fn(string $type) => redirect()->route('register.show', ['type' => $type]));
});

// POST submit actions — only for guests
Route::middleware('guest')->controller(AuthController::class)->group(function () {
    // Single unified login POST
    Route::post('login', 'storeLogin')->name('login.store');

    // Single unified register POST
    Route::post('register', 'register')->name('register.submit');

    // Backward compatibility POST redirects (keep old named routes working for any existing forms)
    Route::post('customer/login', 'storeLogin')->name('customer.login.store');
    Route::post('customer/register', 'register')->name('customer.register.submit');
    Route::post('reseller/login', 'storeLogin')->name('reseller.login.store');
    Route::post('reseller/register', 'register')->name('reseller.register.submit');
    Route::post('register/{type}', 'register')->name('register.submit.typed');
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

    // Show order details page
    Route::get('my-orders/{orderNumber}', [MyOrderController::class, 'show'])->name('my-orders.show');
    Route::patch('my-orders/{orderNumber}/complete', [MyOrderController::class, 'complete'])->name('my-orders.complete');


    Route::middleware(['role:customer,reseller', 'permission:view-my-profile'])
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
