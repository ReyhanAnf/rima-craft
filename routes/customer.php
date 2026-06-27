<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\CatalogController;
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

Route::middleware('guest')->controller(AuthController::class)->group(function () {
    Route::get('login', 'create')->name('login');
    Route::post('login', 'store')->name('login.store');
    Route::get('register/{type}', 'showRegistration')->name('register.show');
    Route::post('register/{type}', 'register')->name('register.submit');
});

Route::middleware('auth')->group(function () {
    Route::post('logout', [AuthController::class, 'destroy'])->name('logout');

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

    Route::middleware(['role:partner', 'permission:view-my-profile'])
        ->prefix('partner')
        ->name('partner.')
        ->controller(PartnerPortalController::class)
        ->group(function () {
            Route::get('/dashboard', 'dashboard')->name('dashboard');
            Route::get('/orders', 'orders')->name('orders');
            Route::get('/billing', 'billing')->name('billing');
            Route::get('/profile', 'profile')->name('profile');
            Route::post('/profile/update', 'updateProfile')->name('profile.update');
        });
});
