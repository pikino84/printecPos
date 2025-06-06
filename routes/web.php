<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\ProductCatalogController;
use App\Http\Controllers\CategoryMappingController;
use App\Http\Controllers\ProviderCategoryMappingController;
use App\Http\Controllers\PrintecCategoryController;
use App\Http\Controllers\ProductWarehouseController;



/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return view('auth.login');
})->middleware('guest')->name('home');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');


// 🔒 Rutas protegidas por autenticación
Route::middleware('auth')->group(function () {
    // Perfil
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // 👮‍♂️ Rutas para usuarios (solo para rol admin)
    Route::middleware(['auth', 'role:admin|super admin'])->group(function () {
        Route::resource('users', UserController::class);
        Route::resource('roles', RoleController::class);
        Route::resource('permissions', PermissionController::class);
        Route::get('/activity-logs', [ActivityLogController::class, 'index'])->name('activity.logs.index');
    });
    
    Route::get('/catalogo', [ProductCatalogController::class, 'index'])->name('catalogo.index');
    //Route::get('/catalogo/fetch', [ProductCatalogController::class, 'fetch'])->name('catalogo.fetch');
    Route::get('/catalogo/{id}', [ProductCatalogController::class, 'show'])->name('catalogo.show');

    // Rutas para la gestión de categorías de Printec
    Route::get('/printec-categories', [PrintecCategoryController::class, 'index']);
    Route::post('/printec-categories', [PrintecCategoryController::class, 'store']);
    Route::delete('/printec-categories/{id}', [PrintecCategoryController::class, 'destroy']);
    Route::put('/printec-categories/{id}', [PrintecCategoryController::class, 'update']);


    // Rutas para asociar categorías de proveedores a categorías de Printec
    Route::get('/category-mappings', [CategoryMappingController::class, 'index']);
    Route::post('/category-mappings/{category}', [CategoryMappingController::class, 'update']);

    // Rutas para poner nicknames a los almacenes
    Route::get('/warehouses', [ProductWarehouseController::class, 'index']);
    Route::put('/warehouses/{id}', [ProductWarehouseController::class, 'update'])->name('warehouses.update');

    Route::resource('asociados', \App\Http\Controllers\AsociadoController::class);

    



});

require __DIR__.'/auth.php';
