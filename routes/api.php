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

// Booking routes
Route::apiResource('bookings', BookingController::class);

// Client routes
Route::apiResource('clients', ClientController::class)->only(['index', 'store', 'show', 'destroy']);
