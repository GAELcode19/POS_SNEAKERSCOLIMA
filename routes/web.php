<?php

use App\Http\Controllers\PosController;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::get('/', function () {
    return redirect()->route('login');
})->name('home');

Route::middleware(['auth'])->group(function () {
    Route::get('dashboard', function () {
        return redirect()->route('pos.terminal');
    })->name('dashboard');

    // Terminal — accesible para todos los empleados
    Route::get('pos', [PosController::class, 'terminal'])->name('pos.terminal');
    Route::post('pos/cart/add', [PosController::class, 'addToCart'])->name('pos.cart.add');
    Route::post('pos/cart/qr', [PosController::class, 'addToCartByQr'])->name('pos.cart.qr');
    Route::post('pos/cart/remove', [PosController::class, 'removeFromCart'])->name('pos.cart.remove');
    Route::post('pos/checkout', [PosController::class, 'checkout'])->name('pos.checkout');
    Route::post('pos/return/search', [PosController::class, 'returnSearch'])->name('pos.return.search');
    Route::post('pos/return/process', [PosController::class, 'returnProcess'])->name('pos.return.process');

    // Admin — solo gerencia y supervisor
    Route::middleware(['admin'])->group(function () {
        Route::get('pos/dashboard', [PosController::class, 'dashboard'])->name('pos.dashboard');
        Route::get('pos/team', [PosController::class, 'team'])->name('pos.team');
        Route::post('pos/team', [PosController::class, 'storeEmployee'])->name('pos.team.store');
        Route::put('pos/team/{user}', [PosController::class, 'updateEmployee'])->name('pos.team.update');
        Route::delete('pos/team/{user}', [PosController::class, 'destroyEmployee'])->name('pos.team.destroy');
        Route::get('pos/alerts', [PosController::class, 'alerts'])->name('pos.alerts');
        Route::get('pos/inventory', [PosController::class, 'inventory'])->name('pos.inventory');
        Route::post('pos/inventory', [PosController::class, 'storeProduct'])->name('pos.inventory.store');
        Route::put('pos/inventory/{product}', [PosController::class, 'updateProduct'])->name('pos.inventory.update');
        Route::post('pos/inventory/{product}/image', [PosController::class, 'updateImage'])->name('pos.inventory.image');
        Route::post('pos/inventory/{product}/sizes', [PosController::class, 'updateSizes'])->name('pos.inventory.sizes');
        Route::delete('pos/inventory/{product}', [PosController::class, 'destroyProduct'])->name('pos.inventory.destroy');
        Route::post('pos/brands', [PosController::class, 'storeBrand'])->name('pos.brands.store');
        Route::delete('pos/brands/{brand}', [PosController::class, 'destroyBrand'])->name('pos.brands.destroy');
        Route::post('pos/categories', [PosController::class, 'storeCategory'])->name('pos.categories.store');
        Route::delete('pos/categories/{category}', [PosController::class, 'destroyCategory'])->name('pos.categories.destroy');
        Route::get('pos/inventory/export', [PosController::class, 'exportInventory'])->name('pos.inventory.export');
        Route::post('pos/team/{user}/avatar', [PosController::class, 'updateAvatar'])->name('pos.team.avatar');
        Route::post('pos/settings/logo', [PosController::class, 'updateLogo'])->name('pos.settings.logo');
    });

    // Settings
    Route::redirect('settings', 'settings/profile');
    Volt::route('settings/profile', 'settings.profile')->name('settings.profile');
    Volt::route('settings/password', 'settings.password')->name('settings.password');
    Volt::route('settings/appearance', 'settings.appearance')->name('settings.appearance');
});

require __DIR__.'/auth.php';
