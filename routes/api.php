<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\RestaurantController;
use App\Http\Controllers\Api\MenuCategoryController;
use App\Http\Controllers\Api\MenuItemController;
use App\Http\Controllers\Api\RestaurantCategoryController;
use App\Http\Controllers\Api\AddressController;
use App\Http\Controllers\Api\RatingController;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Route;

// Autenticación de canales privados de Pusher (requiere Sanctum)
Broadcast::routes(['middleware' => ['auth:sanctum']]);

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/kitchen/login', [AuthController::class, 'kitchenLogin']);
Route::get('/categories', [RestaurantCategoryController::class, 'index']);
Route::get('/announcements', fn() => response()->json([
    'status' => 'success',
    'data'   => \App\Models\Announcement::where('is_active', true)->orderBy('sort_order')->get(),
]));

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/profile', [AuthController::class, 'profile']);
    Route::get('/profile/stats', [AuthController::class, 'stats']);
    Route::post('/profile/change-password', [AuthController::class, 'changePassword']);
    Route::put('/profile', [AuthController::class, 'updateProfile']);
    Route::post('/profile/update', [AuthController::class, 'updateProfile']);
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::post('restaurant/my-data', [RestaurantController::class, 'getMyRestaurantData']);
    Route::get('restaurant/stats', [RestaurantController::class, 'stats']);
    Route::post('restaurant/profile', [RestaurantController::class, 'updateProfile']);
    Route::put('restaurant/profile', [RestaurantController::class, 'updateProfile']);
    Route::apiResource('restaurants', RestaurantController::class);
    
    // Categorías de menú
    Route::apiResource('menu-categories', MenuCategoryController::class)->except(['index']);
    Route::get('restaurants/{restaurant_id}/categories', [MenuCategoryController::class, 'index']);

    // Platillos del menú
    Route::apiResource('menu-items', MenuItemController::class)->except(['index']);
    Route::get('restaurants/{restaurant_id}/items', [MenuItemController::class, 'index']);

    // Favoritos
    Route::get('/favorites', [\App\Http\Controllers\Api\FavoriteController::class, 'index']);
    Route::post('/favorites/{restaurantId}/toggle', [\App\Http\Controllers\Api\FavoriteController::class, 'toggle']);
    Route::get('/favorites/{restaurantId}/check', [\App\Http\Controllers\Api\FavoriteController::class, 'check']);

    // Pedidos
    Route::get('/orders', [\App\Http\Controllers\Api\OrderController::class, 'index']);
    Route::post('/orders', [\App\Http\Controllers\Api\OrderController::class, 'store']);
    Route::get('/orders/{id}', [\App\Http\Controllers\Api\OrderController::class, 'show']);
    Route::put('/orders/{id}/status', [\App\Http\Controllers\Api\OrderController::class, 'updateStatus']);

    // Reseñas
    Route::get('/restaurants/{restaurantId}/ratings', [RatingController::class, 'index']);
    Route::post('/restaurants/{restaurantId}/ratings', [RatingController::class, 'store']);

    // Cocina
    Route::get('/kitchen/orders', [\App\Http\Controllers\Api\OrderController::class, 'kitchenOrders']);
    Route::post('/kitchen/orders/{id}/listo', [\App\Http\Controllers\Api\OrderController::class, 'kitchenMarkListo']);

    // Direcciones del usuario
    Route::get('/addresses', [AddressController::class, 'index']);
    Route::post('/addresses', [AddressController::class, 'store']);
    Route::put('/addresses/{id}', [AddressController::class, 'update']);
    Route::delete('/addresses/{id}', [AddressController::class, 'destroy']);
    Route::post('/addresses/{id}/default', [AddressController::class, 'setDefault']);
});
