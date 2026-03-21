<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\MenuController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\RatingController;
use App\Http\Controllers\Admin\KitchenUserController;
use App\Http\Controllers\Kitchen\AuthController      as KitchenAuthController;
use App\Http\Controllers\Kitchen\DisplayController   as KitchenDisplayController;
use App\Http\Controllers\SuperAdmin\AuthController              as SuperAuthController;
use App\Http\Controllers\SuperAdmin\DashboardController         as SuperDashboardController;
use App\Http\Controllers\SuperAdmin\RestaurantController        as SuperRestaurantController;
use App\Http\Controllers\SuperAdmin\UserController              as SuperUserController;
use App\Http\Controllers\SuperAdmin\RestaurantCategoryController as SuperCategoryController;
use App\Http\Controllers\SuperAdmin\PaymentMethodController     as SuperPaymentController;
use App\Http\Controllers\SuperAdmin\AnnouncementController      as SuperAnnouncementController;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Route;

// Auth de broadcasting para la sesión web (admin)
Broadcast::routes(['middleware' => ['auth']]);

Route::get('/', fn() => view('welcome'));

Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('login', [AuthController::class, 'login'])->name('login.post');
    Route::post('logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

    Route::middleware(['auth', 'admin.owner'])->group(function () {
        Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('orders', [OrderController::class, 'index'])->name('orders');
        Route::get('mesas', [OrderController::class, 'mesas'])->name('mesas');
        Route::get('orders/create', [OrderController::class, 'create'])->name('orders.create');
        Route::post('orders', [OrderController::class, 'store'])->name('orders.store');
        Route::patch('orders/{order}', [OrderController::class, 'update'])->name('orders.update');
        Route::patch('orders/{order}/status', [OrderController::class, 'updateStatus'])->name('orders.status');
        Route::post('tables', [OrderController::class, 'storeTable'])->name('tables.store');
        Route::delete('tables/{table}', [OrderController::class, 'destroyTable'])->name('tables.destroy');
        Route::get('menu', [MenuController::class, 'index'])->name('menu');
        Route::get('customers', [CustomerController::class, 'index'])->name('customers');
        Route::get('ratings', [RatingController::class, 'index'])->name('ratings');

        // Usuarios de cocina
        Route::get('kitchen-users',             [KitchenUserController::class, 'index'])->name('kitchen-users');
        Route::post('kitchen-users',            [KitchenUserController::class, 'store'])->name('kitchen-users.store');
        Route::delete('kitchen-users/{kitchenUser}', [KitchenUserController::class, 'destroy'])->name('kitchen-users.destroy');

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

// ── Cocina ───────────────────────────────────────────────────
Route::prefix('kitchen')->name('kitchen.')->group(function () {
    Route::get('login',  [KitchenAuthController::class, 'showLogin'])->name('login');
    Route::post('login', [KitchenAuthController::class, 'login'])->name('login.post');
    Route::post('logout',[KitchenAuthController::class, 'logout'])->name('logout')->middleware('auth');

    Route::middleware(['auth', 'kitchen'])->group(function () {
        Route::get('display',                   [KitchenDisplayController::class, 'index'])->name('display');
        Route::get('orders/json',               [KitchenDisplayController::class, 'ordersJson'])->name('orders.json');
        Route::post('orders/{order}/listo',     [KitchenDisplayController::class, 'markListo'])->name('orders.listo');
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

        // Restaurant categories
        Route::get('restaurant-categories',                         [SuperCategoryController::class, 'index'])->name('restaurant-categories');
        Route::post('restaurant-categories',                        [SuperCategoryController::class, 'store'])->name('restaurant-categories.store');
        Route::patch('restaurant-categories/{restaurantCategory}',  [SuperCategoryController::class, 'update'])->name('restaurant-categories.update');
        Route::delete('restaurant-categories/{restaurantCategory}', [SuperCategoryController::class, 'destroy'])->name('restaurant-categories.destroy');

        // Payment methods
        Route::get('payment-methods',                    [SuperPaymentController::class, 'index'])->name('payment-methods');
        Route::post('payment-methods',                   [SuperPaymentController::class, 'store'])->name('payment-methods.store');
        Route::patch('payment-methods/{paymentMethod}',  [SuperPaymentController::class, 'update'])->name('payment-methods.update');
        Route::delete('payment-methods/{paymentMethod}', [SuperPaymentController::class, 'destroy'])->name('payment-methods.destroy');

        // Announcements
        Route::get('announcements',                      [SuperAnnouncementController::class, 'index'])->name('announcements');
        Route::post('announcements',                     [SuperAnnouncementController::class, 'store'])->name('announcements.store');
        Route::patch('announcements/{announcement}',     [SuperAnnouncementController::class, 'update'])->name('announcements.update');
        Route::delete('announcements/{announcement}',    [SuperAnnouncementController::class, 'destroy'])->name('announcements.destroy');
        Route::patch('announcements/{announcement}/toggle', [SuperAnnouncementController::class, 'toggle'])->name('announcements.toggle');
    });
});
