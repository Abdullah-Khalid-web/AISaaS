<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\AI\Api\LicenseApiController;

Route::prefix('v1')->group(function () {
    // Auth routes
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']);

    // PUBLIC SDK / App routes
    Route::post('/verify-license', [LicenseApiController::class, 'verifyLicense']);
    Route::get('/license-status', [LicenseApiController::class, 'getLicenseStatus']);
    Route::post('/register-device', [LicenseApiController::class, 'registerDevice']);
    Route::post('/remove-device', [LicenseApiController::class, 'removeDevice']);
    Route::post('/use-tool', [LicenseApiController::class, 'useTool']);
});

// Only dashboard user routes
Route::prefix('v1')->middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
});
