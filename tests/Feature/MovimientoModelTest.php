<?php

use App\Models\Destino;
use App\Models\InventarioTienda;
use App\Models\Movimiento;
use App\Models\Producto;
use App\Models\Tienda;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Crear datos base para todos los tests
    $this->user = User::factory()->create();
    $this->tienda = Tienda::factory()->create(['is_active' => true]);
    $this->producto = Producto::factory()->create(['activo' => true]);
    $this->destino = Destino::factory()->create();

    $this->inventario = InventarioTienda::factory()->create([
        'tienda_id' => $this->tienda->id,
        'producto_id' => $this->producto->id,
        'cantidad' => 100,
    ]);
});

describe('Movimiento Model Events', function () {
    it('actualiza el inventario correctamente al crear un movimiento con entradas', function () {
        $cantidadInicial = $this->inventario->cantidad;

        Movimiento::create([
            'inventario_tienda_id' => $this->inventario->id,
            'producto_id' => $this->producto->id,
            'usuario_id' => $this->user->id,
            'entradas' => 50,
            'salidas' => 0,
            'traslados' => 0,
            'venta_diaria' => 0,
        ]);

        $this->inventario->refresh();
        expect($this->inventario->cantidad)->toBe($cantidadInicial + 50);
    });

    it('actualiza el inventario correctamente al crear un movimiento con traslados', function () {
        $cantidadInicial = $this->inventario->cantidad;

        Movimiento::create([
            'inventario_tienda_id' => $this->inventario->id,
            'producto_id' => $this->producto->id,
            'usuario_id' => $this->user->id,
            'entradas' => 0,
            'salidas' => 0,
            'traslados' => 30,
            'venta_diaria' => 0,
        ]);

        $this->inventario->refresh();
        expect($this->inventario->cantidad)->toBe($cantidadInicial - 30);
    });

    it('actualiza el inventario correctamente al crear un movimiento con venta_diaria', function () {
        $cantidadInicial = $this->inventario->cantidad;

        Movimiento::create([
            'inventario_tienda_id' => $this->inventario->id,
            'producto_id' => $this->producto->id,
            'usuario_id' => $this->user->id,
            'entradas' => 0,
            'salidas' => 0,
            'traslados' => 0,
            'venta_diaria' => 20,
        ]);

        $this->inventario->refresh();
        expect($this->inventario->cantidad)->toBe($cantidadInicial - 20);
    });

    it('actualiza el inventario correctamente con entradas, traslados y venta_diaria combinados', function () {
        $cantidadInicial = $this->inventario->cantidad;

        Movimiento::create([
            'inventario_tienda_id' => $this->inventario->id,
            'producto_id' => $this->producto->id,
            'usuario_id' => $this->user->id,
            'entradas' => 100,
            'salidas' => 0,
            'traslados' => 30,
            'venta_diaria' => 20,
        ]);

        $this->inventario->refresh();
        // 100 (inicial) + 100 (entradas) - 30 (traslados) - 20 (venta_diaria) = 150
        expect($this->inventario->cantidad)->toBe($cantidadInicial + 100 - 30 - 20);
    });

    it('ajusta el inventario correctamente al actualizar un movimiento', function () {
        $movimiento = Movimiento::create([
            'inventario_tienda_id' => $this->inventario->id,
            'producto_id' => $this->producto->id,
            'usuario_id' => $this->user->id,
            'entradas' => 50,
            'salidas' => 0,
            'traslados' => 10,
            'venta_diaria' => 5,
        ]);

        $this->inventario->refresh();
        $cantidadDespuesCrear = $this->inventario->cantidad;

        // Actualizar el movimiento
        $movimiento->update([
            'entradas' => 80,
            'traslados' => 20,
            'venta_diaria' => 10,
        ]);

        $this->inventario->refresh();
        // Diferencia: (80 - 20 - 10) - (50 - 10 - 5) = 50 - 35 = +15
        expect($this->inventario->cantidad)->toBe($cantidadDespuesCrear + 15);
    });

    it('restaura el inventario correctamente al eliminar un movimiento', function () {
        $cantidadInicial = $this->inventario->cantidad;

        $movimiento = Movimiento::create([
            'inventario_tienda_id' => $this->inventario->id,
            'producto_id' => $this->producto->id,
            'usuario_id' => $this->user->id,
            'entradas' => 50,
            'salidas' => 0,
            'traslados' => 20,
            'venta_diaria' => 10,
        ]);

        $this->inventario->refresh();
        expect($this->inventario->cantidad)->toBe($cantidadInicial + 50 - 20 - 10);

        // Eliminar el movimiento
        $movimiento->delete();

        $this->inventario->refresh();
        expect($this->inventario->cantidad)->toBe($cantidadInicial);
    });

    it('maneja correctamente el cambio de inventario_tienda_id', function () {
        // Crear segundo inventario
        $tienda2 = Tienda::factory()->create(['is_active' => true]);
        $inventario2 = InventarioTienda::factory()->create([
            'tienda_id' => $tienda2->id,
            'producto_id' => $this->producto->id,
            'cantidad' => 50,
        ]);

        $cantidadInicial1 = $this->inventario->cantidad;
        $cantidadInicial2 = $inventario2->cantidad;

        // Crear movimiento en inventario 1
        $movimiento = Movimiento::create([
            'inventario_tienda_id' => $this->inventario->id,
            'producto_id' => $this->producto->id,
            'usuario_id' => $this->user->id,
            'entradas' => 30,
            'salidas' => 0,
            'traslados' => 10,
            'venta_diaria' => 5,
        ]);

        $this->inventario->refresh();
        expect($this->inventario->cantidad)->toBe($cantidadInicial1 + 30 - 10 - 5);

        // Cambiar a inventario 2
        $movimiento->update([
            'inventario_tienda_id' => $inventario2->id,
        ]);

        $this->inventario->refresh();
        $inventario2->refresh();

        // Inventario 1 debe restaurarse
        expect($this->inventario->cantidad)->toBe($cantidadInicial1);
        // Inventario 2 debe aplicar el movimiento
        expect($inventario2->cantidad)->toBe($cantidadInicial2 + 30 - 10 - 5);
    });
});

describe('Movimiento Model Relationships', function () {
    it('tiene relación con producto', function () {
        $movimiento = Movimiento::create([
            'inventario_tienda_id' => $this->inventario->id,
            'producto_id' => $this->producto->id,
            'usuario_id' => $this->user->id,
            'entradas' => 10,
            'salidas' => 0,
            'traslados' => 0,
            'venta_diaria' => 0,
        ]);

        expect($movimiento->producto)->toBeInstanceOf(Producto::class);
        expect($movimiento->producto->id)->toBe($this->producto->id);
    });

    it('tiene relación con destino', function () {
        $movimiento = Movimiento::create([
            'inventario_tienda_id' => $this->inventario->id,
            'producto_id' => $this->producto->id,
            'usuario_id' => $this->user->id,
            'destino_id' => $this->destino->id,
            'entradas' => 10,
            'salidas' => 0,
            'traslados' => 0,
            'venta_diaria' => 0,
        ]);

        expect($movimiento->destino)->toBeInstanceOf(Destino::class);
        expect($movimiento->destino->id)->toBe($this->destino->id);
    });

    it('tiene relación con usuario', function () {
        $movimiento = Movimiento::create([
            'inventario_tienda_id' => $this->inventario->id,
            'producto_id' => $this->producto->id,
            'usuario_id' => $this->user->id,
            'entradas' => 10,
            'salidas' => 0,
            'traslados' => 0,
            'venta_diaria' => 0,
        ]);

        expect($movimiento->usuario)->toBeInstanceOf(User::class);
        expect($movimiento->usuario->id)->toBe($this->user->id);
    });

    it('tiene relación con inventario_tienda', function () {
        $movimiento = Movimiento::create([
            'inventario_tienda_id' => $this->inventario->id,
            'producto_id' => $this->producto->id,
            'usuario_id' => $this->user->id,
            'entradas' => 10,
            'salidas' => 0,
            'traslados' => 0,
            'venta_diaria' => 0,
        ]);

        expect($movimiento->inventario_tienda)->toBeInstanceOf(InventarioTienda::class);
        expect($movimiento->inventario_tienda->id)->toBe($this->inventario->id);
    });

    it('tiene relación con tienda_relacionada', function () {
        $tiendaRelacionada = Tienda::factory()->create(['is_active' => true]);

        $movimiento = Movimiento::create([
            'inventario_tienda_id' => $this->inventario->id,
            'producto_id' => $this->producto->id,
            'usuario_id' => $this->user->id,
            'tienda_relacionada_id' => $tiendaRelacionada->id,
            'entradas' => 10,
            'salidas' => 0,
            'traslados' => 0,
            'venta_diaria' => 0,
        ]);

        expect($movimiento->tienda_relacionada)->toBeInstanceOf(Tienda::class);
        expect($movimiento->tienda_relacionada->id)->toBe($tiendaRelacionada->id);
    });
});

describe('Movimiento Model with Decimal Values', function () {
    it('maneja correctamente valores decimales', function () {
        $cantidadInicial = $this->inventario->cantidad;

        Movimiento::create([
            'inventario_tienda_id' => $this->inventario->id,
            'producto_id' => $this->producto->id,
            'usuario_id' => $this->user->id,
            'entradas' => 25.5,
            'salidas' => 0,
            'traslados' => 10.25,
            'venta_diaria' => 5.75,
        ]);

        $this->inventario->refresh();
        expect($this->inventario->cantidad)->toBe($cantidadInicial + 25.5 - 10.25 - 5.75);
    });
});
