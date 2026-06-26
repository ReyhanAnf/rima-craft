<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Portal\CustomerPortalController;
use App\Http\Controllers\Portal\PartnerPortalController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\OrderController;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\CatalogController;

Route::get('/', [CatalogController::class, 'index'])->name('catalog.index');
Route::get('/katalog/filter', [CatalogController::class, 'filter'])->name('catalog.filter');

// Static Pages Routes
Route::get('/syarat-ketentuan', function () {
    return view('page', ['title' => 'Syarat & Ketentuan', 'content' => config('settings.page_terms')]);
})->name('page.terms');

Route::get('/kebijakan-privasi', function () {
    return view('page', ['title' => 'Kebijakan Privasi', 'content' => config('settings.page_privacy')]);
})->name('page.privacy');

Route::get('/pengiriman-retur', function () {
    return view('page', ['title' => 'Pengiriman & Retur', 'content' => config('settings.page_shipping')]);
})->name('page.shipping');

// Public Order Routes (No login required)
Route::prefix('order')->group(function () {
    Route::get('/checkout', function () {
        return view('orders.checkout');
    })->name('order.checkout');
    Route::post('/store', [OrderController::class, 'store'])->name('order.store');
    Route::get('/success/{order}', [OrderController::class, 'success'])->name('order.success');
});

Route::middleware('guest')->group(function () {
    // Admin Login Routes
    Route::get('admin/login', [AuthController::class, 'createAdminLogin'])->name('admin.login');
    Route::post('admin/login', [AuthController::class, 'storeAdminLogin'])->name('admin.login.store');
    
    // Backward Compatibility - Redirect to admin login
    Route::get('login', [AuthController::class, 'create'])->name('login');
    Route::post('login', [AuthController::class, 'store'])->name('login.store');
    
    // Registration Routes
    Route::get('register/{type}', [AuthController::class, 'showRegistration'])->name('register.show');
    Route::post('register/{type}', [AuthController::class, 'register'])->name('register.submit');
});

Route::middleware('auth')->group(function () {
    Route::post('logout', [AuthController::class, 'destroy'])->name('logout');
    
    // Dashboard - All authenticated users
    Route::get('/dashboard', [\App\Http\Controllers\DashboardController::class, 'index'])
        ->middleware('permission:view-dashboard')
        ->name('dashboard');
    
    // Profile - All authenticated users
    Route::get('/profile', [\App\Http\Controllers\ProfileController::class, 'edit'])->name('profile.edit');
    Route::post('/profile', [\App\Http\Controllers\ProfileController::class, 'update'])->name('profile.update');

    // Products - Permission protected
    Route::middleware('permission:view-products')->group(function () {
        Route::resource('products', \App\Http\Controllers\ProductController::class)->except(['show']);
        Route::delete('products/{product}/media/{index}', [\App\Http\Controllers\ProductController::class, 'destroyMedia'])
            ->middleware('permission:edit-products')
            ->name('products.media.destroy');
    });

    // Materials - Permission protected
    Route::middleware('permission:view-materials')->group(function () {
        Route::resource('materials', \App\Http\Controllers\MaterialController::class)->except(['show']);
    });

    // Stock Adjustments - Permission protected
    Route::middleware('permission:adjust-stock')->group(function () {
        Route::resource('stock-adjustments', \App\Http\Controllers\StockAdjustmentController::class)
            ->only(['index', 'create', 'store']);
    });

    // Contacts - Permission protected
    Route::middleware('permission:view-contacts')->group(function () {
        Route::resource('contacts', \App\Http\Controllers\ContactController::class)->except(['show']);
    });

    // Purchases - Permission protected
    Route::middleware('permission:view-purchases')->group(function () {
        Route::resource('purchases', \App\Http\Controllers\PurchaseController::class)
            ->except(['edit', 'update', 'destroy']);
    });

    // Sales - Permission protected
    Route::middleware('permission:view-sales')->group(function () {
        Route::get('sales/{sale}/print', [\App\Http\Controllers\SaleController::class, 'printInvoice'])
            ->middleware('permission:print-sales')
            ->name('sales.print');
        Route::resource('sales', \App\Http\Controllers\SaleController::class)
            ->except(['edit', 'update', 'destroy']);
        Route::patch('sales/{sale}/status', [\App\Http\Controllers\SaleController::class, 'updateStatus'])
            ->middleware('permission:update-sales-status')
            ->name('sales.update-status');
    });

    // Productions - Permission protected
    Route::middleware('permission:view-productions')->group(function () {
        Route::resource('productions', \App\Http\Controllers\ProductionController::class)
            ->except(['edit', 'update', 'destroy']);
    });
    
    // Finance Routes - Permission protected
    Route::middleware('permission:view-finance')->group(function () {
        Route::get('finance', [\App\Http\Controllers\FinanceController::class, 'index'])->name('finance.index');
        Route::get('finance/print', [\App\Http\Controllers\FinanceController::class, 'printReport'])
            ->middleware('permission:print-finance-reports')
            ->name('finance.print');
        Route::post('finance/accounts', [\App\Http\Controllers\FinanceController::class, 'storeAccount'])
            ->middleware('permission:manage-accounts')
            ->name('finance.accounts.store');
        Route::post('finance/transactions', [\App\Http\Controllers\FinanceController::class, 'storeTransaction'])
            ->middleware('permission:record-transactions')
            ->name('finance.transactions.store');
    });
    
    // Payments - Permission protected
    Route::middleware('permission:record-payments')->group(function () {
        Route::post('payments', [\App\Http\Controllers\PaymentController::class, 'store'])->name('payments.store');
    });
    
    // Settings Routes - Permission protected
    Route::middleware('permission:view-settings')->group(function () {
        Route::get('settings', [\App\Http\Controllers\SettingController::class, 'index'])->name('settings.index');
        Route::post('settings', [\App\Http\Controllers\SettingController::class, 'update'])
            ->middleware('permission:manage-settings')
            ->name('settings.update');
    });

    // Gallery Routes - Permission protected
    Route::middleware('permission:view-gallery')->group(function () {
        Route::resource('galleries', \App\Http\Controllers\GalleryController::class)
            ->only(['index', 'store', 'destroy']);
    });
    
    // Customer Portal Routes (B2C)
    Route::middleware(['role:customer', 'permission:view-my-profile'])->prefix('customer')->name('customer.')->group(function () {
        Route::get('/dashboard', [CustomerPortalController::class, 'dashboard'])->name('dashboard');
        Route::get('/orders', [CustomerPortalController::class, 'orders'])->name('orders');
        Route::get('/profile', [CustomerPortalController::class, 'profile'])->name('profile');
        Route::post('/profile/update', [CustomerPortalController::class, 'updateProfile'])->name('profile.update');
    });
    
    // Partner Portal Routes (B2B/Reseller)
    Route::middleware(['role:partner', 'permission:view-my-profile'])->prefix('partner')->name('partner.')->group(function () {
        Route::get('/dashboard', [PartnerPortalController::class, 'dashboard'])->name('dashboard');
        Route::get('/orders', [PartnerPortalController::class, 'orders'])->name('orders');
        Route::get('/billing', [PartnerPortalController::class, 'billing'])->name('billing');
        Route::get('/profile', [PartnerPortalController::class, 'profile'])->name('profile');
        Route::post('/profile/update', [PartnerPortalController::class, 'updateProfile'])->name('profile.update');
    });
    
    // User Management Routes (Super Admin only)
    Route::middleware(['permission:manage-users'])->group(function () {
        Route::resource('users', UserController::class)->except(['show']);
    });
    
    // Role Management Routes (Super Admin only)
    Route::middleware(['permission:manage-roles'])->group(function () {
        Route::get('roles', [RoleController::class, 'index'])->name('roles.index');
        Route::get('roles/{role}/edit', [RoleController::class, 'edit'])->name('roles.edit');
        Route::put('roles/{role}', [RoleController::class, 'update'])->name('roles.update');
        Route::delete('roles/{role}', [RoleController::class, 'destroy'])->name('roles.destroy');
    });
});
