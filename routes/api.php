<?php

use App\Http\Controllers\Api\PublicCatalogController;
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
