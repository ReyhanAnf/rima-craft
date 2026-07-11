<?php

use App\Http\Controllers\AdminOrderController;
use App\Http\Controllers\ArtisanJobController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FinanceController;
use App\Http\Controllers\GalleryController;
use App\Http\Controllers\MaterialController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductionController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\StockAdjustmentController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')
    ->controller(AuthController::class)
    ->group(function () {
        Route::get('admin/login', 'createAdminLogin')->name('admin.login');
        Route::post('admin/login', 'storeAdminLogin')->name('admin.login.store');
    });

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->middleware('permission:view-dashboard')
        ->name('dashboard');

    Route::controller(ProfileController::class)->group(function () {
        Route::get('/profile', 'edit')->name('profile.edit');
        Route::post('/profile', 'update')->name('profile.update');
    });

    Route::middleware('permission:view-products')->group(function () {
        Route::resource('products', ProductController::class)->except(['show']);
        Route::delete('products/{product}/media/{index}', [ProductController::class, 'destroyMedia'])
            ->middleware('permission:edit-products')
            ->name('products.media.destroy');
    });

    Route::middleware('permission:view-materials')->group(function () {
        Route::resource('materials', MaterialController::class)->except(['show']);
    });

    Route::middleware('permission:adjust-stock')->group(function () {
        Route::resource('stock-adjustments', StockAdjustmentController::class)->only(['index', 'create', 'store']);
    });

    Route::middleware('permission:view-contacts')->group(function () {
        Route::resource('contacts', ContactController::class)->except(['show']);
    });

    Route::middleware('permission:view-purchases')->group(function () {
        Route::resource('purchases', PurchaseController::class)->except(['edit', 'update', 'destroy']);
    });

    Route::middleware('permission:view-sales')->controller(SaleController::class)->group(function () {
        Route::get('sales/{sale}/print', 'printInvoice')
            ->middleware('permission:print-sales')
            ->name('sales.print');
        Route::resource('sales', SaleController::class)->except(['edit', 'update', 'destroy']);
        Route::patch('sales/{sale}/status', 'updateStatus')
            ->middleware('permission:update-sales-status')
            ->name('sales.update-status');
    });

    Route::middleware('permission:view-orders')
        ->controller(AdminOrderController::class)
        ->group(function () {
            Route::get('orders', 'index')->name('orders.index');
            Route::get('orders/{order}', 'show')->name('orders.show');
            Route::patch('orders/{order}/status', 'updateStatus')
                ->middleware('permission:manage-orders')
                ->name('orders.update-status');
            Route::delete('orders/{order}', 'destroy')
                ->middleware('permission:manage-orders')
                ->name('orders.destroy');
        });

    Route::middleware('permission:view-productions')->group(function () {
        Route::resource('productions', ProductionController::class)->except(['edit', 'update', 'destroy']);
    });

    Route::middleware('permission:manage-artisan-jobs')->group(function () {
        Route::resource('artisan-jobs', ArtisanJobController::class)->except(['create', 'show', 'edit']);
    });

    Route::middleware('permission:view-finance')
        ->prefix('finance')
        ->name('finance.')
        ->controller(FinanceController::class)
        ->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/print', 'printReport')
                ->middleware('permission:print-finance-reports')
                ->name('print');
            Route::post('/accounts', 'storeAccount')
                ->middleware('permission:manage-accounts')
                ->name('accounts.store');
            Route::post('/transactions', 'storeTransaction')
                ->middleware('permission:record-transactions')
                ->name('transactions.store');
        });

    Route::middleware('permission:record-payments')->controller(PaymentController::class)->group(function () {
        Route::post('payments', 'store')->name('payments.store');
    });

    Route::middleware('permission:view-settings')
        ->prefix('settings')
        ->name('settings.')
        ->controller(SettingController::class)
        ->group(function () {
            Route::get('/', 'index')->name('index');
            Route::post('/', 'update')
                ->middleware('permission:manage-settings')
                ->name('update');
        });

    Route::middleware('permission:view-settings')->group(function () {
        Route::resource('payment-methods', \App\Http\Controllers\AdminPaymentMethodController::class)->except(['show']);
        Route::resource('regions', \App\Http\Controllers\AdminRegionController::class)->except(['create', 'show', 'edit']);
    });

    Route::middleware('permission:view-gallery')->group(function () {
        Route::resource('galleries', GalleryController::class)->only(['index', 'store', 'destroy']);
    });

    Route::middleware('permission:manage-users')->group(function () {
        Route::resource('users', UserController::class)->except(['show']);
        Route::patch('users/{user}/verify-reseller', [UserController::class, 'verifyReseller'])->name('users.verify-reseller');
        Route::patch('users/{user}/reject-reseller', [UserController::class, 'rejectReseller'])->name('users.reject-reseller');
    });

    Route::middleware('permission:manage-roles')
        ->controller(RoleController::class)
        ->group(function () {
            Route::get('roles', 'index')->name('roles.index');
            Route::get('roles/{role}/edit', 'edit')->name('roles.edit');
            Route::put('roles/{role}', 'update')->name('roles.update');
            Route::delete('roles/{role}', 'destroy')->name('roles.destroy');
        });
});
