<?php

use App\Http\Controllers\Api\PropertyController;
use App\Http\Controllers\Api\UserPreferenceController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\SocialAuthController;
use App\Http\Controllers\Api\AuthController;


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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::prefix('auth')->group(function () {
    Route::get('{provider}/redirect', [SocialAuthController::class, 'redirect']);
    Route::get('{provider}/callback', [SocialAuthController::class, 'callback']);
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);
    Route::post('/login', [AuthController::class, 'login']);
});

Route::prefix('v1')->group(function () {
    // Protected Routes (Require Token)
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/user', [AuthController::class, 'me']);
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::post('/user-preferences', [UserPreferenceController::class, 'store']);

        // Get Preferences (Optional, for re-populating the form)
        Route::get('/user-preferences', [UserPreferenceController::class, 'show']);

    });
    Route::get('/preference-options', [UserPreferenceController::class, 'getOptions']);
    Route::get('/properties-kre', [PropertyController::class, 'index']);
    Route::get('/properties/{id}', [PropertyController::class, 'show']);

});
