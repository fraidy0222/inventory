<?php

use App\Http\Controllers\ProductoController;
use App\Http\Controllers\UsersController;
use App\Http\Controllers\TiendaController;
use App\Http\Controllers\InventarioTiendaController;
use App\Http\Controllers\DestinoController;
use App\Http\Controllers\FilesController;
use App\Http\Controllers\MovimientoController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;

// Route::get('/', function () {
//     return Inertia::render('Welcome', [
//         'canRegister' => Features::enabled(Features::registration()),
//     ]);
// })->name('home');

Route::get('/', function () {
    return Inertia::render('auth/Login', ['canRegister' => Features::enabled(Features::registration())]);
})->name('home');


Route::middleware(['auth', 'verified'])->group(function () {

    Route::get('dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');

    Route::resource('usuarios', UsersController::class);
    Route::resource('tiendas', TiendaController::class);
    Route::resource('productos', ProductoController::class);
    Route::resource('inventarioTienda', InventarioTiendaController::class);
    Route::resource('destinos', DestinoController::class);
    Route::resource('movimientos', MovimientoController::class);
    Route::get(
        '/api/productos-no-asignados/{tiendaId}',
        [InventarioTiendaController::class, 'getProductosNoAsignados']
    );
    Route::get(
        '/api/movimientos/inventario/{tiendaId}/{productoId}',
        [MovimientoController::class, 'getInventarioTiendaId']
    );
    Route::get(
        '/api/movimientos/productos-en-tienda/{tiendaId}',
        [MovimientoController::class, 'getProductosEnTienda']
    );
    Route::get(
        '/api/movimientos/check-stock/{productoId}',
        [MovimientoController::class, 'checkStockAvailability']
    );
    Route::post(
        '/api/movimientos/transfer-and-use',
        [MovimientoController::class, 'transferAndUse']
    );
    Route::get('archivos/download/{fileName}', [FilesController::class, 'download'])->name('archivos.download');
    Route::resource('archivos', FilesController::class);
});
require __DIR__ . '/settings.php';
