<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\MenuController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\RatingController;
use App\Http\Controllers\SuperAdmin\AuthController      as SuperAuthController;
use App\Http\Controllers\SuperAdmin\DashboardController as SuperDashboardController;
use App\Http\Controllers\SuperAdmin\RestaurantController as SuperRestaurantController;
use App\Http\Controllers\SuperAdmin\UserController      as SuperUserController;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Route;

// Auth de broadcasting para la sesión web (admin)
Broadcast::routes(['middleware' => ['auth']]);

Route::get('/', fn() => redirect('/admin/login'));

Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('login', [AuthController::class, 'login'])->name('login.post');
    Route::post('logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

    Route::middleware(['auth', 'admin.owner'])->group(function () {
        Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('orders', [OrderController::class, 'index'])->name('orders');
        Route::patch('orders/{order}/status', [OrderController::class, 'updateStatus'])->name('orders.status');
        Route::get('menu', [MenuController::class, 'index'])->name('menu');
        Route::get('customers', [CustomerController::class, 'index'])->name('customers');
        Route::get('ratings', [RatingController::class, 'index'])->name('ratings');

        // Categories
        Route::post('menu/categories', [MenuController::class, 'storeCategory'])->name('menu.categories.store');
        Route::patch('menu/categories/{category}', [MenuController::class, 'updateCategory'])->name('menu.categories.update');
        Route::delete('menu/categories/{category}', [MenuController::class, 'destroyCategory'])->name('menu.categories.destroy');

        // Items
        Route::post('menu/items', [MenuController::class, 'storeItem'])->name('menu.items.store');
        Route::patch('menu/items/{item}', [MenuController::class, 'updateItem'])->name('menu.items.update');
        Route::delete('menu/items/{item}', [MenuController::class, 'destroyItem'])->name('menu.items.destroy');
        Route::patch('menu/items/{item}/toggle', [MenuController::class, 'toggleItem'])->name('menu.items.toggle');
    });
});

// ── Super Admin ──────────────────────────────────────────────
Route::prefix('superadmin')->name('superadmin.')->group(function () {
    Route::get('login',  [SuperAuthController::class, 'showLogin'])->name('login');
    Route::post('login', [SuperAuthController::class, 'login'])->name('login.post');
    Route::post('logout',[SuperAuthController::class, 'logout'])->name('logout')->middleware('auth');

    Route::middleware(['auth', 'superadmin'])->group(function () {
        Route::get('dashboard',    [SuperDashboardController::class, 'index'])->name('dashboard');
        Route::get('restaurants',  [SuperRestaurantController::class, 'index'])->name('restaurants');
        Route::patch('restaurants/{restaurant}/toggle', [SuperRestaurantController::class, 'toggleActive'])->name('restaurants.toggle');
        Route::get('users',        [SuperUserController::class, 'index'])->name('users');
    });
});
