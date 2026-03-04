<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\RestaurantController;
use App\Http\Controllers\Api\MenuCategoryController;
use App\Http\Controllers\Api\MenuItemController;
use App\Http\Controllers\Api\RestaurantCategoryController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::get('/categories', [RestaurantCategoryController::class, 'index']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/profile', [AuthController::class, 'profile']);
    Route::put('/profile', [AuthController::class, 'updateProfile']);
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::apiResource('restaurants', RestaurantController::class);
    
    // Categorías de menú
    Route::apiResource('menu-categories', MenuCategoryController::class)->except(['index']);
    Route::get('restaurants/{restaurant_id}/categories', [MenuCategoryController::class, 'index']);

    // Platillos del menú
    Route::apiResource('menu-items', MenuItemController::class)->except(['index']);
    Route::get('restaurants/{restaurant_id}/items', [MenuItemController::class, 'index']);
});
