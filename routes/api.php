<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\RestaurantController;
use App\Http\Controllers\Api\MenuCategoryController;
use App\Http\Controllers\Api\MenuItemController;
use App\Http\Controllers\Api\RestaurantCategoryController;
use App\Http\Controllers\Api\AddressController;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Route;

// Autenticación de canales privados de Pusher (requiere Sanctum)
Broadcast::routes(['middleware' => ['auth:sanctum']]);

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::get('/categories', [RestaurantCategoryController::class, 'index']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/profile', [AuthController::class, 'profile']);
    Route::put('/profile', [AuthController::class, 'updateProfile']);
    Route::post('/profile/update', [AuthController::class, 'updateProfile']);
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::apiResource('restaurants', RestaurantController::class);
    Route::post('restaurant/my-data', [RestaurantController::class, 'getMyRestaurantData']);
    Route::post('restaurant/profile', [RestaurantController::class, 'updateProfile']);
    Route::put('restaurant/profile', [RestaurantController::class, 'updateProfile']);
    
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

    // Direcciones del usuario
    Route::get('/addresses', [AddressController::class, 'index']);
    Route::post('/addresses', [AddressController::class, 'store']);
    Route::put('/addresses/{id}', [AddressController::class, 'update']);
    Route::delete('/addresses/{id}', [AddressController::class, 'destroy']);
    Route::post('/addresses/{id}/default', [AddressController::class, 'setDefault']);
});
