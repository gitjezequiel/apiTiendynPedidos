<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json([
        'name'    => 'Tiendyn Pedidos API',
        'version' => '1.0.0',
        'status'  => 'online',
        'docs'    => url('/api'),
    ]);
});
