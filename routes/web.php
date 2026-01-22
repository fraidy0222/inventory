<?php

use App\Http\Controllers\ProductoController;
use App\Http\Controllers\UsersController;
use App\Http\Controllers\TiendaController;
use App\Http\Controllers\InventarioTiendaController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canRegister' => Features::enabled(Features::registration()),
    ]);
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::resource('usuarios', UsersController::class);
Route::resource('tiendas', TiendaController::class);
Route::resource('productos', ProductoController::class);
Route::resource('inventarioTienda', InventarioTiendaController::class);

require __DIR__ . '/settings.php';
