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
use App\Http\Controllers\MyWarehouseController;
use App\Http\Controllers\MyCategoryController;
use App\Http\Controllers\PricingTierController;
use App\Http\Controllers\PartnerPricingController;
use App\Http\Controllers\PricingDashboardController;
use App\Http\Controllers\PricingReportController;
use App\Http\Controllers\PartnerRegistrationController;
use App\Http\Controllers\PricingSettingController;
use App\Http\Controllers\TourController;



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

    // Tour de usuario
    Route::get('/tour/status', [TourController::class, 'status'])->name('tour.status');
    Route::post('/tour/complete', [TourController::class, 'complete'])->name('tour.complete');
    Route::post('/tour/reset', [TourController::class, 'reset'])->name('tour.reset');

    // 👮‍♂️ Rutas para usuarios (solo para rol admin)
    Route::middleware(['auth', 'role:admin|super admin|Asociado Administrador'])->group(function () {
        Route::resource('partners', PartnerController::class);
        //usuarios por socio
        Route::get('partners/{partner}/users', [PartnerController::class, 'users'])->name('partners.users');
        //Productos por socio
        Route::get('partners/{partner}/products', [PartnerController::class, 'products'])->name('partners.products');
        // API del catálogo para partners
        Route::post('partners/{partner}/generate-api-key', [PartnerController::class, 'generateApiKey'])->name('partners.generate-api-key');
        Route::post('partners/{partner}/revoke-api-key', [PartnerController::class, 'revokeApiKey'])->name('partners.revoke-api-key');
        Route::put('partners/{partner}/api-settings', [PartnerController::class, 'updateApiSettings'])->name('partners.api-settings');

        Route::resource('users', UserController::class);
        Route::resource('roles', RoleController::class);
        Route::resource('permissions', PermissionController::class);
    });

    // ========================================================================
    // Historial de Actividad (protegido por permiso)
    // ========================================================================
    Route::middleware(['permission:activity-logs.view'])->group(function () {
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
    Route::get('api/own-products/search', [OwnProductController::class, 'search'])->name('own-products.search');
    Route::post('own-products/{own_product}/duplicate', [OwnProductController::class, 'duplicate'])->name('own-products.duplicate');
    Route::resource('own-products', OwnProductController::class);



    Route::resource('partners.entities', PartnerEntityController::class)->parameters(['entities' => 'entity'])->shallow();
    Route::resource('partner-entities.bank-accounts', PartnerEntityBankAccountController::class)->parameters(['bank-accounts' => 'bank_account'])->shallow();
    Route::resource('partner-products', PartnerProductController::class)->parameters(['partner-products' => 'product'])->shallow();

    // ========================================================================
    // Razones Sociales (protegido por permisos)
    // ========================================================================
    Route::middleware(['permission:razones-sociales.view'])->group(function () {
        Route::get('/razones-sociales', [PartnerEntityController::class, 'myIndex'])->name('my-entities.index');
    });
    Route::middleware(['permission:razones-sociales.manage'])->group(function () {
        Route::get('/razones-sociales/create', [PartnerEntityController::class, 'myCreate'])->name('my-entities.create');
        Route::post('/razones-sociales', [PartnerEntityController::class, 'myStore'])->name('my-entities.store');
        Route::get('/razones-sociales/{id}/edit', [PartnerEntityController::class, 'myEdit'])->name('my-entities.edit');
        Route::put('/razones-sociales/{id}', [PartnerEntityController::class, 'myUpdate'])->name('my-entities.update');
        Route::delete('/razones-sociales/{id}', [PartnerEntityController::class, 'myDestroy'])->name('my-entities.destroy');
        // Configuración de correo por razón social
        Route::get('/razones-sociales/{id}/mail-config', [PartnerEntityController::class, 'mailConfig'])->name('my-entities.mail-config');
        Route::put('/razones-sociales/{id}/mail-config', [PartnerEntityController::class, 'mailConfigUpdate'])->name('my-entities.mail-config.update');
        Route::post('/razones-sociales/{id}/mail-config/test', [PartnerEntityController::class, 'mailConfigTest'])->name('my-entities.mail-config.test');
    });

    // ========================================================================
    // PRICING - Dashboard (requiere permiso pricing-dashboard.view)
    // ========================================================================
    Route::middleware(['permission:pricing-dashboard.view'])->group(function () {
        Route::get('pricing-dashboard', [PricingDashboardController::class, 'index'])->name('pricing-dashboard.index');
    });

    // ========================================================================
    // PRICING - Niveles de Precio (pricing-tiers)
    // ========================================================================
    Route::middleware(['permission:pricing-tiers.manage'])->group(function () {
        Route::get('pricing-tiers/create', [PricingTierController::class, 'create'])->name('pricing-tiers.create');
        Route::post('pricing-tiers', [PricingTierController::class, 'store'])->name('pricing-tiers.store');
        Route::get('pricing-tiers/{pricing_tier}/edit', [PricingTierController::class, 'edit'])->name('pricing-tiers.edit');
        Route::put('pricing-tiers/{pricing_tier}', [PricingTierController::class, 'update'])->name('pricing-tiers.update');
        Route::delete('pricing-tiers/{pricing_tier}', [PricingTierController::class, 'destroy'])->name('pricing-tiers.destroy');
    });
    Route::middleware(['permission:pricing-tiers.view'])->group(function () {
        Route::get('pricing-tiers', [PricingTierController::class, 'index'])->name('pricing-tiers.index');
        Route::get('pricing-tiers/{pricing_tier}', [PricingTierController::class, 'show'])->name('pricing-tiers.show');
    });

    // ========================================================================
    // PRICING - Pricing de Partners (partner-pricing)
    // ========================================================================
    Route::middleware(['permission:partner-pricing.view'])->group(function () {
        Route::get('partner-pricing', [PartnerPricingController::class, 'index'])->name('partner-pricing.index');
        Route::get('partner-pricing/{partner}/history', [PartnerPricingController::class, 'history'])->name('partner-pricing.history');
    });
    Route::middleware(['permission:partner-pricing.manage'])->group(function () {
        Route::get('partner-pricing/{partner}/edit', [PartnerPricingController::class, 'edit'])->name('partner-pricing.edit');
        Route::put('partner-pricing/{partner}', [PartnerPricingController::class, 'update'])->name('partner-pricing.update');
        Route::post('partner-pricing/{partner}/reset-purchases', [PartnerPricingController::class, 'resetPurchases'])->name('partner-pricing.reset-purchases');
    });

    // ========================================================================
    // PRICING - Reportes (pricing-reports)
    // ========================================================================
    Route::middleware(['permission:pricing-reports.view'])->group(function () {
        Route::get('pricing-reports/tier-history', [PricingReportController::class, 'tierHistory'])->name('pricing-reports.tier-history');
        Route::get('pricing-reports/monthly-purchases', [PricingReportController::class, 'monthlyPurchases'])->name('pricing-reports.monthly-purchases');
        Route::get('pricing-reports/partner-evolution', [PricingReportController::class, 'partnerEvolution'])->name('pricing-reports.partner-evolution');
        Route::get('pricing-reports/export-tier-history', [PricingReportController::class, 'exportTierHistory'])->name('pricing-reports.export-tier-history');
    });

    // ========================================================================
    // PRICING - Configuración (solo super admin)
    // ========================================================================
    Route::middleware(['permission:pricing-settings.view'])->group(function () {
        Route::get('pricing-settings', [PricingSettingController::class, 'index'])->name('pricing-settings.index');
    });
    Route::middleware(['permission:pricing-settings.manage'])->group(function () {
        Route::put('pricing-settings', [PricingSettingController::class, 'update'])->name('pricing-settings.update');
    });

    // ========================================================================
    // Mi Ganancia (Markup del Asociado)
    // ========================================================================
    Route::middleware(['role:Asociado Administrador|Asociado Vendedor'])->group(function () {
        Route::get('/mi-ganancia', [PartnerPricingController::class, 'myMarkup'])->name('my-markup.index');
        Route::put('/mi-ganancia', [PartnerPricingController::class, 'updateMyMarkup'])->name('my-markup.update');
    });

    // ========================================================================
    // Cuentas Bancarias (protegido por permisos)
    // ========================================================================
    Route::middleware(['permission:cuentas-bancarias.view'])->group(function () {
        Route::get('/cuentas-bancarias', [PartnerEntityBankAccountController::class, 'myIndex'])->name('my-bank-accounts.index');
    });
    Route::middleware(['permission:cuentas-bancarias.manage'])->group(function () {
        Route::get('/cuentas-bancarias/create', [PartnerEntityBankAccountController::class, 'myCreate'])->name('my-bank-accounts.create');
        Route::post('/cuentas-bancarias', [PartnerEntityBankAccountController::class, 'myStore'])->name('my-bank-accounts.store');
        Route::get('/cuentas-bancarias/{id}/edit', [PartnerEntityBankAccountController::class, 'myEdit'])->name('my-bank-accounts.edit');
        Route::put('/cuentas-bancarias/{id}', [PartnerEntityBankAccountController::class, 'myUpdate'])->name('my-bank-accounts.update');
        Route::delete('/cuentas-bancarias/{id}', [PartnerEntityBankAccountController::class, 'myDestroy'])->name('my-bank-accounts.destroy');
    });

    // ========================================================================
    // Usuarios para Asociados (MIS USUARIOS)
    // ========================================================================
    Route::middleware(['role:Asociado Administrador|Asociado Vendedor|super admin'])->group(function () {
        // Todos los usuarios de asociados pueden ver
        Route::get('/mis-usuarios', [UserController::class, 'myIndex'])->name('my-users.index');
    });

    Route::middleware(['role:Asociado Administrador|super admin'])->group(function () {
        // Solo Asociado Administrador puede crear/editar/eliminar
        Route::get('/mis-usuarios/create', [UserController::class, 'myCreate'])->name('my-users.create');
        Route::post('/mis-usuarios', [UserController::class, 'myStore'])->name('my-users.store');
        Route::get('/mis-usuarios/{id}/edit', [UserController::class, 'myEdit'])->name('my-users.edit');
        Route::put('/mis-usuarios/{id}', [UserController::class, 'myUpdate'])->name('my-users.update');
        Route::delete('/mis-usuarios/{id}', [UserController::class, 'myDestroy'])->name('my-users.destroy');
    });

    // ========================================================================
    // Almacenes para Asociados (MIS ALMACENES) 🆕
    // ========================================================================
    Route::middleware(['role:Asociado Administrador|Asociado Vendedor|super admin'])->group(function () {
        // Todos pueden ver
        Route::get('/mis-almacenes', [MyWarehouseController::class, 'index'])->name('my-warehouses.index');
    });

    Route::middleware(['role:Asociado Administrador|super admin'])->group(function () {
        // Solo Asociado Administrador puede crear/editar/eliminar
        Route::get('/mis-almacenes/create', [MyWarehouseController::class, 'create'])->name('my-warehouses.create');
        Route::post('/mis-almacenes', [MyWarehouseController::class, 'store'])->name('my-warehouses.store');
        Route::get('/mis-almacenes/{id}/edit', [MyWarehouseController::class, 'edit'])->name('my-warehouses.edit');
        Route::put('/mis-almacenes/{id}', [MyWarehouseController::class, 'update'])->name('my-warehouses.update');
        Route::delete('/mis-almacenes/{id}', [MyWarehouseController::class, 'destroy'])->name('my-warehouses.destroy');
    });

    // ========================================================================
    // Categorías para Asociados (MIS CATEGORÍAS) 🆕
    // ========================================================================
    Route::middleware(['role:Asociado Administrador|Asociado Vendedor|super admin'])->group(function () {
        // Todos pueden ver
        Route::get('/mis-categorias', [MyCategoryController::class, 'index'])->name('my-categories.index');
    });

    Route::middleware(['role:Asociado Administrador|super admin'])->group(function () {
        // Solo Asociado Administrador puede crear/editar/eliminar
        Route::post('/mis-categorias', [MyCategoryController::class, 'store'])->name('my-categories.store');
        Route::put('/mis-categorias/{id}', [MyCategoryController::class, 'update'])->name('my-categories.update');
        Route::delete('/mis-categorias/{id}', [MyCategoryController::class, 'destroy'])->name('my-categories.destroy');
    });

    // ========================================================================
    // RUTAS DEL CARRITO
    // ========================================================================
    Route::prefix('cart')->name('cart.')->group(function () {
        Route::get('/', [CartController::class, 'index'])->name('index');
        Route::post('/add', [CartController::class, 'add'])->name('add');
        Route::get('/count', [CartController::class, 'count'])->name('count');
        Route::get('/preview-pdf', [CartController::class, 'previewPdf'])->name('preview-pdf');
        Route::get('/import', [CartController::class, 'showImport'])->name('import');
        Route::post('/import', [CartController::class, 'processImport'])->name('import.process');
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
        Route::post('/{quote}/accept', [QuoteController::class, 'accept'])->name('accept');
        Route::post('/{quote}/reject', [QuoteController::class, 'reject'])->name('reject');
        Route::post('/{quote}/invoice', [QuoteController::class, 'invoice'])->name('invoice');
        Route::post('/{quote}/expired', [QuoteController::class, 'expired'])->name('expired');
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
// ========================================================================
// REGISTRO PÚBLICO DE PARTNERS
// ========================================================================
Route::middleware('guest')->group(function () {
    Route::get('/registro', [PartnerRegistrationController::class, 'showRegistrationForm'])->name('partner.registration.form');
    Route::post('/registro', [PartnerRegistrationController::class, 'register'])->name('partner.registration.submit');
    Route::get('/registro-exitoso', [PartnerRegistrationController::class, 'success'])->name('partner.registration.success');
});
require __DIR__.'/auth.php';
