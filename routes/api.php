<?php

use App\Http\Controllers\Api\AuthController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes untuk Aplikasi Mobile Nasabah
|--------------------------------------------------------------------------
|
| Rute-rute berikut diproteksi atau diproses melalui otentikasi Sanctum.
| Mendukung pemanggilan langsung (/api/...) maupun ber-prefix (/api/auth/...).
|
*/

Route::post('/register', [AuthController::class, 'register'])
    ->middleware('throttle:5,1')
    ->name('api.register');
Route::post('/login', [AuthController::class, 'login'])->name('api.login');

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('api.logout');
    Route::get('/me', [AuthController::class, 'me'])->name('api.me');
});

// Alias dengan prefix /auth untuk fleksibilitas klien mobile
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register'])
        ->middleware('throttle:5,1')
        ->name('api.auth.register');
    Route::post('/login', [AuthController::class, 'login'])->name('api.auth.login');

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout'])->name('api.auth.logout');
        Route::get('/me', [AuthController::class, 'me'])->name('api.auth.me');
    });
});
