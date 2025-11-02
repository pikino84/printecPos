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
use App\Http\Controllers\ProductWarehousesCitiesController;
use App\Http\Controllers\PartnerController;
use App\Http\Controllers\PartnerEntityController;
use App\Http\Controllers\PartnerEntityBankAccountController;
use App\Http\Controllers\PartnerProductController;
use App\Http\Controllers\OwnProductController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\QuoteController;
use App\Http\Controllers\ClientController;



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
    Route::middleware(['auth', 'role:admin|super admin|Asociado Administrador'])->group(function () {
        Route::resource('partners', PartnerController::class);
        //usuarios por socio
        Route::get('partners/{partner}/users', [PartnerController::class, 'users'])->name('partners.users');
        //Productos por socio
        Route::get('partners/{partner}/products', [PartnerController::class, 'products'])->name('partners.products');

        Route::resource('users', UserController::class);
        Route::resource('roles', RoleController::class);
        Route::resource('permissions', PermissionController::class);
        Route::get('/activity-logs', [ActivityLogController::class, 'index'])->name('activity.logs.index');
    });
    
    Route::get('/catalogo', [ProductCatalogController::class, 'index'])->name('catalogo.index');
    //Route::get('/catalogo/fetch', [ProductCatalogController::class, 'fetch'])->name('catalogo.fetch');
    Route::get('/catalogo/{id}', [ProductCatalogController::class, 'show'])->name('catalogo.show');

    // 🆕 API para obtener almacenes por partner (para formularios dinámicos)
    Route::get('/api/partners/{partner}/warehouses', [ProductCatalogController::class, 'getWarehousesByPartner'])
        ->name('api.partners.warehouses');

    // Rutas para la gestión de categorías de Printec
    Route::get('/printec-categories', [PrintecCategoryController::class, 'index']);
    Route::post('/printec-categories', [PrintecCategoryController::class, 'store']);
    Route::delete('/printec-categories/{id}', [PrintecCategoryController::class, 'destroy']);
    Route::put('/printec-categories/{id}', [PrintecCategoryController::class, 'update']);
    // Rutas para la gestión de ciudades donde estan los almacenes de los proveedores de Printec
    Route::get('/printec-cities', [ProductWarehousesCitiesController::class, 'index']);
    Route::post('/printec-cities', [ProductWarehousesCitiesController::class, 'store']);
    Route::put('/printec-cities/{id}', [ProductWarehousesCitiesController::class, 'update']);
    Route::delete('/printec-cities/{id}', [ProductWarehousesCitiesController::class, 'destroy']);


    // Rutas para asociar categorías de proveedores a categorías de Printec
    Route::get('/category-mappings', [CategoryMappingController::class, 'index']);
    Route::post('/category-mappings/{category}', [CategoryMappingController::class, 'update']);

    // Rutas para gestión completa de almacenes (warehouses)
    Route::middleware('auth')->group(function () {
        Route::get('/warehouses', [ProductWarehouseController::class, 'index'])->name('warehouses.index');
        Route::get('/warehouses/create', [ProductWarehouseController::class, 'create'])->name('warehouses.create');
        Route::post('/warehouses', [ProductWarehouseController::class, 'store'])->name('warehouses.store');
        Route::get('/warehouses/{id}/edit', [ProductWarehouseController::class, 'edit'])->name('warehouses.edit');
        Route::put('/warehouses/{id}', [ProductWarehouseController::class, 'update'])->name('warehouses.update');
        Route::delete('/warehouses/{id}', [ProductWarehouseController::class, 'destroy'])->name('warehouses.destroy');
    });

    // Productos propios
    Route::resource('own-products', OwnProductController::class);
    Route::get('api/own-products/search', [OwnProductController::class, 'search'])->name('own-products.search');
    Route::post('own-products/{ownProduct}/duplicate', [OwnProductController::class, 'duplicate'])->name('own-products.duplicate');



    Route::resource('partners', PartnerController::class)->middleware('auth');
    Route::resource('partners.entities', PartnerEntityController::class)->parameters(['entities' => 'entity'])->shallow();
    Route::resource('partner-entities.bank-accounts', PartnerEntityBankAccountController::class)->parameters(['bank-accounts' => 'bank_account'])->shallow();
    Route::resource('partner-products', PartnerProductController::class)->parameters(['partner-products' => 'product'])->shallow();

    
    // ========================================================================
    // RUTAS DEL CARRITO
    // ========================================================================
    Route::prefix('cart')->name('cart.')->group(function () {
        Route::get('/', [CartController::class, 'index'])->name('index');
        Route::post('/add', [CartController::class, 'add'])->name('add');
        Route::get('/count', [CartController::class, 'count'])->name('count');
        Route::patch('/{item}', [CartController::class, 'update'])->name('update');
        Route::delete('/{item}', [CartController::class, 'destroy'])->name('destroy');
        Route::delete('/', [CartController::class, 'clear'])->name('clear');
    });

    // ========================================================================
    // RUTAS DE COTIZACIONES
    // ========================================================================
    Route::prefix('quotes')->name('quotes.')->group(function () {
        Route::get('/', [QuoteController::class, 'index'])->name('index');
        Route::post('/create-from-cart', [QuoteController::class, 'createFromCart'])->name('create');
        Route::get('/{quote}', [QuoteController::class, 'show'])->name('show');
        Route::post('/{quote}/send', [QuoteController::class, 'send'])->name('send');
        Route::get('/{quote}/pdf', [QuoteController::class, 'downloadPdf'])->name('pdf');
        Route::post('/{quote}/clone-to-cart', [QuoteController::class, 'cloneToCart'])->name('clone-to-cart');
        Route::post('/{quote}/edit-to-cart', [QuoteController::class, 'editToCart'])->name('edit-to-cart');
        Route::delete('/{quote}', [QuoteController::class, 'destroy'])->name('destroy');
    });

    // ========================================================================
    // RUTAS DE CLIENTES
    // ========================================================================
    Route::prefix('clients')->name('clients.')->group(function () {
        Route::get('/', [ClientController::class, 'index'])->name('index');
        Route::get('/create', [ClientController::class, 'create'])->name('create');
        Route::post('/', [ClientController::class, 'store'])->name('store');
        Route::get('/{client}', [ClientController::class, 'show'])->name('show');
        Route::get('/{client}/edit', [ClientController::class, 'edit'])->name('edit');
        Route::put('/{client}', [ClientController::class, 'update'])->name('update');
        Route::delete('/{client}', [ClientController::class, 'destroy'])->name('destroy');
        
        // API para búsqueda
        Route::get('/api/search', [ClientController::class, 'search'])->name('search');
    });


});

require __DIR__.'/auth.php';