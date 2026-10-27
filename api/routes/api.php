<?php

use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\ProductPriceController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Rutas protegidas con limitación de tasa (rate limiting) para evitar
| abusos y ataques de denegación de servicio (DoS).
|
*/

Route::middleware(['throttle:60,1'])->group(function () {
    // Endpoints RESTful estándar para productos
    Route::apiResource('products', ProductController::class);

    // Endpoints para gestión de precios multidivisa asociados a un producto
    Route::get('products/{product}/prices', [ProductPriceController::class, 'index']);
    Route::post('products/{product}/prices', [ProductPriceController::class, 'store']);
});
