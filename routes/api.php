<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\GovernmentSchemeController;

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

Route::middleware('api')->group(function () {
    // Government Scheme API endpoints
    Route::prefix('government-schemes')->group(function () {
        // Submit new scheme
        Route::post('/submit', [GovernmentSchemeController::class, 'submitScheme'])->name('api.government-schemes.submit');
        
        // Update existing scheme
        Route::put('/{schemeId}', [GovernmentSchemeController::class, 'updateScheme'])->name('api.government-schemes.update');
        
        // Get scheme status
        Route::get('/{schemeId}/status', [GovernmentSchemeController::class, 'getSchemeStatus'])->name('api.government-schemes.status');
    });
}); 