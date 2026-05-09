<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GoodsReceiptController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PosController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\PurchaseOrderController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\Settings\PaymentGatewayController;
use App\Http\Controllers\Settings\RoleManagementController;
use App\Http\Controllers\Settings\StoreSettingController;
use App\Http\Controllers\Settings\UserManagementController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\UnitController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Guest routes (belum login)
|--------------------------------------------------------------------------
*/

Route::middleware('guest')->group(function () {
    Route::get('/login',         [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login',        [LoginController::class, 'login']);
});

/*
|--------------------------------------------------------------------------
| Authenticated routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
    Route::get('/',        [DashboardController::class, 'index'])->name('dashboard');

    // ── Master Data ──
    Route::resource('categories', CategoryController::class);
    Route::patch('categories/{category}/toggle-status', [CategoryController::class, 'toggleStatus'])
        ->name('categories.toggle-status');

    Route::resource('units',      UnitController::class);
    Route::patch('units/{unit}/toggle-status', [UnitController::class, 'toggleStatus'])
        ->name('units.toggle-status');

    Route::resource('products',   ProductController::class);
    Route::patch('products/{product}/toggle-status', [ProductController::class, 'toggleStatus'])
        ->name('products.toggle-status');

    Route::resource('suppliers',  SupplierController::class);
    Route::patch('suppliers/{supplier}/toggle-status', [SupplierController::class, 'toggleStatus'])
        ->name('suppliers.toggle-status');

    Route::resource('customers',  CustomerController::class);
    Route::patch('customers/{customer}/toggle-status', [CustomerController::class, 'toggleStatus'])
        ->name('customers.toggle-status');

    // ── Pembelian ──
    Route::resource('purchase-orders', PurchaseOrderController::class);
    Route::patch('purchase-orders/{purchaseOrder}/send', [PurchaseOrderController::class, 'send'])
        ->name('purchase-orders.send');

    // Route::resource('goods-receipts',  GoodsReceiptController::class);

    Route::resource('goods-receipts', GoodsReceiptController::class)->only(['index', 'show', 'create', 'store']);

    // Route::post('goods-receipts/{gr}/confirm', [GoodsReceiptController::class, 'confirm']);

    Route::get('inventory', [InventoryController::class, 'index'])->name('inventory.index');
    Route::get('inventory/{product}', [InventoryController::class, 'show'])->name('inventory.show');
    Route::get('inventory/{product}/stock-card', [InventoryController::class, 'stockCard'])->name('inventory.stock-card');

    // ── POS ──
    Route::prefix('pos')->name('pos.')->group(function () {
        Route::get('/',              [PosController::class, 'index'])->name('index');
        Route::post('/',             [PosController::class, 'store'])->name('store');
        Route::get('/receipt/{sale}', [PosController::class, 'receipt'])->name('receipt');
        Route::get('/stock/{product}', [PosController::class, 'checkStock'])->name('check-stock');
    });

    // ── Laporan ──
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('hpp',              [ReportController::class, 'hpp'])->name('hpp');
        Route::get('sales',            [ReportController::class, 'sales'])->name('sales');
        Route::get('sales/{sale}',     [ReportController::class, 'saleDetail'])->name('sale-detail');
        Route::get('inventory',        [ReportController::class, 'inventory'])->name('inventory');
    });

    // ── Payment Gateway (Midtrans) ──
    Route::prefix('payment')->name('payment.')->group(function () {
        Route::post('snap-token',            [PaymentController::class, 'createSnapToken'])->name('snap-token');
        Route::post('complete/{transaction}', [PaymentController::class, 'complete'])->name('complete');
        Route::post('cancel/{transaction}',   [PaymentController::class, 'cancel'])->name('cancel');
    });

    // ── Pengaturan ──
    Route::prefix('settings')->name('settings.')->group(function () {
        Route::get('/', fn() => redirect()->route('settings.index'))->name('redirect');
        Route::get('/dashboard', function () { return view('settings.index'); })->name('index')->middleware('can:settings.view');

        // Informasi Toko
        Route::get('store',        [StoreSettingController::class, 'index'])->name('store');
        Route::put('store',        [StoreSettingController::class, 'update'])->name('store.update');

        // Payment Gateway
        Route::get('payment-gateway',  [PaymentGatewayController::class, 'index'])->name('payment-gateway');
        Route::put('payment-gateway',  [PaymentGatewayController::class, 'update'])->name('payment-gateway.update');

        // Manajemen User
        Route::get('users',                     [UserManagementController::class, 'index'])->name('users.index');
        Route::get('users/create',              [UserManagementController::class, 'create'])->name('users.create');
        Route::post('users',                    [UserManagementController::class, 'store'])->name('users.store');
        Route::get('users/{user}/edit',         [UserManagementController::class, 'edit'])->name('users.edit');
        Route::put('users/{user}',              [UserManagementController::class, 'update'])->name('users.update');
        Route::delete('users/{user}',           [UserManagementController::class, 'destroy'])->name('users.destroy');
        Route::patch('users/{user}/toggle-status', [UserManagementController::class, 'toggleStatus'])->name('users.toggle-status');

        // Manajemen Role & Permissions
        Route::get('roles',           [RoleManagementController::class, 'index'])->name('roles.index');
        Route::get('roles/{role}/edit', [RoleManagementController::class, 'edit'])->name('roles.edit');
        Route::put('roles/{role}',    [RoleManagementController::class, 'update'])->name('roles.update');
    });
});

// Midtrans notification webhook (tidak perlu auth)
Route::post('payment/notification', [PaymentController::class, 'notification'])
    ->name('payment.notification')
    ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class]);
