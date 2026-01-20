<?php

use App\Http\Controllers\Api\PublicCatalogController;
use App\Http\Controllers\Api\CartImportController;
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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

/*
|--------------------------------------------------------------------------
| Public Catalog API (for Partners/Distributors)
|--------------------------------------------------------------------------
*/
Route::prefix('public/catalog')->middleware('partner.api')->group(function () {
    Route::get('/info', [PublicCatalogController::class, 'info']);
    Route::get('/categories', [PublicCatalogController::class, 'categories']);
    Route::get('/products', [PublicCatalogController::class, 'index']);
    Route::get('/products/{id}', [PublicCatalogController::class, 'show']);
});

/*
|--------------------------------------------------------------------------
| Cart Import API (for importing carts from external widget)
|--------------------------------------------------------------------------
*/
Route::prefix('cart')->middleware('auth:sanctum')->group(function () {
    Route::post('/import', [CartImportController::class, 'import'])->name('api.cart.import');
    Route::post('/validate-import', [CartImportController::class, 'validate'])->name('api.cart.validate');
});
