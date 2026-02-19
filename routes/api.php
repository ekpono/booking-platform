<?php

use App\Http\Controllers\Api\BookingController;
use App\Http\Controllers\Api\ClientController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group.
|
*/

// API root endpoint
Route::get('/', function () {
    return response()->json([
        'name' => 'Booking Platform API',
        'version' => '1.0.0',
        'endpoints' => [
            'bookings' => '/api/bookings',
            'clients' => '/api/clients',
        ],
    ]);
});

// Protected routes - require authentication
Route::middleware('auth:sanctum')->group(function () {
    // Booking routes
    Route::apiResource('bookings', BookingController::class);

    // Client routes
    Route::apiResource('clients', ClientController::class)->only(['index', 'store', 'show', 'destroy']);
});
