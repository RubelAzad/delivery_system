<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DeliveryZoneController;


Route::middleware('auth:sanctum')->group(function () {
    Route::post('/delivery-zones', [DeliveryZoneController::class, 'createZone'])
        ->name('api.delivery-zones.store');

    Route::post('/orders/validate', [DeliveryZoneController::class, 'validateOrder'])
        ->name('api.orders.validate');
});

