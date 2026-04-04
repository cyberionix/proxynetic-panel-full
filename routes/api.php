<?php

use App\Http\Controllers\ApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('basic.token')->group( function () {
    Route::get('/get-auth-tokens',[ApiController::class,'getAuthTokens'])->name('api.get-auth-tokens');
    Route::get('/get-tunnel-details',[ApiController::class,'getTunnelDetails'])->name('api.get-tunnel-details');
    Route::get('/get-ip-history',[ApiController::class,'getIpHistory'])->name('api.get-ip-history');
    Route::post('/create-tunnel',[ApiController::class,'createTunnel'])->name('api.create-tunnel');
    Route::post('/set-expiration-date-for-tunnel',[ApiController::class,'setExpirationDateForTunnel'])->name('api.set-expiration-date');
    Route::post('/update-ip-change-link',[ApiController::class,'updateIpChangeLink'])->name('api.update-ip-change-link');
});

Route::post('/tunnel-manager',[ApiController::class,'proxyManagerActions'])->name('api.proxy-manager-actions');
Route::post('/get-tunnel-manager',[ApiController::class,'getTunnelManager'])->name('api.get-tunnel-manager');
