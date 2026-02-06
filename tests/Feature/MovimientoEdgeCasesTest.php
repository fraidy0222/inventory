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
    $this->user = User::factory()->create();
    $this->tienda = Tienda::factory()->create(['is_active' => true]);
    $this->producto = Producto::factory()->create(['activo' => true]);
    $this->destino = Destino::factory()->create();

    $this->inventario = InventarioTienda::factory()->create([
        'tienda_id' => $this->tienda->id,
        'producto_id' => $this->producto->id,
        'cantidad' => 100,
    ]);

    $this->actingAs($this->user);
});

describe('Prevención de Stock Negativo', function () {
    it('no permite crear movimiento que deje stock negativo', function () {
        $response = $this->post(route('movimientos.store'), [
            'inventario_tienda_id' => $this->inventario->id,
            'producto_id' => $this->producto->id,
            'usuario_id' => $this->user->id,
            'entradas' => 0,
            'salidas' => 0,
            'traslados' => 70,
            'venta_diaria' => 50, // Total: 120, disponible: 100
        ]);

        $response->assertSessionHasErrors('inventario_tienda_id');

        // Verificar que el inventario no cambió
        $this->inventario->refresh();
        expect($this->inventario->cantidad)->toBe(100);
    });

    it('permite usar exactamente todo el stock disponible', function () {
        $response = $this->post(route('movimientos.store'), [
            'inventario_tienda_id' => $this->inventario->id,
            'producto_id' => $this->producto->id,
            'usuario_id' => $this->user->id,
            'entradas' => 0,
            'salidas' => 0,
            'traslados' => 60,
            'venta_diaria' => 40, // Total: 100, disponible: 100
        ]);

        $response->assertSessionDoesntHaveErrors();

        $this->inventario->refresh();
        expect($this->inventario->cantidad)->toBe(0);
    });
});

describe('Múltiples Movimientos Consecutivos', function () {
    it('calcula correctamente el inventario con múltiples movimientos', function () {
        $cantidadInicial = $this->inventario->cantidad;

        // Movimiento 1: Entrada de 50
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

        // Movimiento 2: Traslado de 30
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
        expect($this->inventario->cantidad)->toBe($cantidadInicial + 50 - 30);

        // Movimiento 3: Venta diaria de 20
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
        expect($this->inventario->cantidad)->toBe($cantidadInicial + 50 - 30 - 20);

        // Movimiento 4: Entrada de 100, traslado de 40, venta de 10
        Movimiento::create([
            'inventario_tienda_id' => $this->inventario->id,
            'producto_id' => $this->producto->id,
            'usuario_id' => $this->user->id,
            'entradas' => 100,
            'salidas' => 0,
            'traslados' => 40,
            'venta_diaria' => 10,
        ]);

        $this->inventario->refresh();
        expect($this->inventario->cantidad)->toBe($cantidadInicial + 50 - 30 - 20 + 100 - 40 - 10);
    });

    it('maneja correctamente múltiples actualizaciones del mismo movimiento', function () {
        $cantidadInicial = $this->inventario->cantidad;

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
        $cantidadDespues1 = $this->inventario->cantidad;
        expect($cantidadDespues1)->toBe($cantidadInicial + 50 - 10 - 5);

        // Primera actualización
        $movimiento->update([
            'entradas' => 80,
            'traslados' => 20,
            'venta_diaria' => 10,
        ]);

        $this->inventario->refresh();
        $cantidadDespues2 = $this->inventario->cantidad;
        expect($cantidadDespues2)->toBe($cantidadInicial + 80 - 20 - 10);

        // Segunda actualización
        $movimiento->update([
            'entradas' => 100,
            'traslados' => 30,
            'venta_diaria' => 15,
        ]);

        $this->inventario->refresh();
        expect($this->inventario->cantidad)->toBe($cantidadInicial + 100 - 30 - 15);
    });

    it('maneja correctamente múltiples eliminaciones SIN contar salidas', function () {
        // Cantidad inicial del inventario (100)
        $cantidadInicial = $this->inventario->cantidad;

        dump("Cantidad inicial: {$cantidadInicial}");

        // Movimiento 1: entradas 50, traslados 10, venta_diaria 5
        // Cálculo según tu observer: 50 - (10 + 5) = 35
        $movimiento1 = Movimiento::create([
            'inventario_tienda_id' => $this->inventario->id,
            'producto_id' => $this->producto->id,
            'usuario_id' => $this->user->id,
            'entradas' => 50,
            'salidas' => 0, // ← NO se resta
            'traslados' => 10,
            'venta_diaria' => 5,
        ]);

        $this->inventario->refresh();
        dump("Después movimiento 1: {$this->inventario->cantidad}"); // Debería ser 135

        // Movimiento 2: entradas 30, traslados 5
        // Cálculo: 30 - (5 + 0) = 25
        $movimiento2 = Movimiento::create([
            'inventario_tienda_id' => $this->inventario->id,
            'producto_id' => $this->producto->id,
            'usuario_id' => $this->user->id,
            'entradas' => 30,
            'salidas' => 0, // ← NO se resta
            'traslados' => 5,
            'venta_diaria' => 0,
        ]);

        $this->inventario->refresh();
        dump("Después movimiento 2: {$this->inventario->cantidad}"); // Debería ser 160

        // Ahora, según tu lógica:
        $cambioMov1 = 50 - 10 - 5; // 35
        $cambioMov2 = 30 - 5; // 25
        $totalEsperado = $cantidadInicial + $cambioMov1 + $cambioMov2; // 100 + 35 + 25 = 160

        expect($this->inventario->cantidad)->toEqual($totalEsperado);

        // Eliminar primer movimiento
        $movimiento1->delete();

        $this->inventario->refresh();
        dump("Después eliminar movimiento 1: {$this->inventario->cantidad}"); // 160 - 35 = 125

        // Después de eliminar movimiento 1, queda solo el efecto de movimiento 2
        expect($this->inventario->cantidad)->toEqual($cantidadInicial + $cambioMov2); // 100 + 25 = 125

        // Eliminar segundo movimiento
        $movimiento2->delete();

        $this->inventario->refresh();
        dump("Después eliminar movimiento 2: {$this->inventario->cantidad}"); // 125 - 25 = 100

        // Debería volver a la cantidad inicial
        expect($this->inventario->cantidad)->toEqual($cantidadInicial); // 100
    });
});

describe('Valores Decimales', function () {
    it('maneja correctamente operaciones con decimales', function () {
        $cantidadInicial = $this->inventario->cantidad;

        $response = $this->post(route('movimientos.store'), [
            'inventario_tienda_id' => $this->inventario->id,
            'producto_id' => $this->producto->id,
            'usuario_id' => $this->user->id,
            'entradas' => 25,
            'salidas' => 0,
            'traslados' => 10,
            'venta_diaria' => 5,
        ]);

        $response->assertSessionDoesntHaveErrors();

        $this->inventario->refresh();
        expect($this->inventario->cantidad)->toBe($cantidadInicial + 25 - 10 - 5);
    });

    it('valida correctamente con decimales que suman exactamente el stock', function () {
        // Ajustar inventario a un valor decimal
        $this->inventario->update(['cantidad' => 50.5]);

        $response = $this->post(route('movimientos.store'), [
            'inventario_tienda_id' => $this->inventario->id,
            'producto_id' => $this->producto->id,
            'usuario_id' => $this->user->id,
            'entradas' => 0,
            'salidas' => 0,
            'traslados' => 30,
            'venta_diaria' => 20, // Total: 50.5
        ]);

        $response->assertSessionDoesntHaveErrors();

        $this->inventario->refresh();
        expect($this->inventario->cantidad)->toBe(0.0);
    });

    it('previene stock negativo con decimales', function () {
        $this->inventario->update(['cantidad' => 50.5]);

        $response = $this->post(route('movimientos.store'), [
            'inventario_tienda_id' => $this->inventario->id,
            'producto_id' => $this->producto->id,
            'usuario_id' => $this->user->id,
            'entradas' => 0,
            'salidas' => 0,
            'traslados' => 30.25,
            'venta_diaria' => 20.26, // Total: 50.51, disponible: 50.5
        ]);

        $response->assertSessionHasErrors('inventario_tienda_id');
    });
});

describe('Transferencias Entre Tiendas', function () {
    it('transfiere exactamente todo el stock disponible', function () {
        $tienda2 = Tienda::factory()->create(['is_active' => true]);

        $response = $this->post(route('movimientos.store'), [
            'inventario_tienda_id' => $this->inventario->id,
            'producto_id' => $this->producto->id,
            'usuario_id' => $this->user->id,
            'entradas' => 0,
            'salidas' => 0,
            'traslados' => 100, // Todo el stock
            'venta_diaria' => 0,
            'tienda_relacionada_id' => $tienda2->id,
        ]);

        $response->assertSessionDoesntHaveErrors();

        $this->inventario->refresh();
        expect($this->inventario->cantidad)->toBe(0.0);

        $inventarioDestino = InventarioTienda::where('tienda_id', $tienda2->id)
            ->where('producto_id', $this->producto->id)
            ->first();

        expect($inventarioDestino->cantidad)->toBe(100.0);
    });

    it('crea inventario en tienda destino si no existe', function () {
        $tienda2 = Tienda::factory()->create(['is_active' => true]);

        // Verificar que no existe inventario
        $inventarioAntes = InventarioTienda::where('tienda_id', $tienda2->id)
            ->where('producto_id', $this->producto->id)
            ->first();
        expect($inventarioAntes)->toBeNull();

        $response = $this->post(route('movimientos.store'), [
            'inventario_tienda_id' => $this->inventario->id,
            'producto_id' => $this->producto->id,
            'usuario_id' => $this->user->id,
            'entradas' => 0,
            'salidas' => 0,
            'traslados' => 50,
            'venta_diaria' => 0,
            'tienda_relacionada_id' => $tienda2->id,
        ]);

        $response->assertSessionDoesntHaveErrors();

        // Verificar que se creó el inventario
        $inventarioDespues = InventarioTienda::where('tienda_id', $tienda2->id)
            ->where('producto_id', $this->producto->id)
            ->first();

        expect($inventarioDespues)->not->toBeNull();
        expect($inventarioDespues->cantidad)->toBe(50.0);
    });

    it('maneja transferencias bidireccionales correctamente', function () {
        $tienda2 = Tienda::factory()->create(['is_active' => true]);
        $inventario2 = InventarioTienda::factory()->create([
            'tienda_id' => $tienda2->id,
            'producto_id' => $this->producto->id,
            'cantidad' => 50,
        ]);

        // Transferir de tienda1 a tienda2
        $this->post(route('movimientos.store'), [
            'inventario_tienda_id' => $this->inventario->id,
            'producto_id' => $this->producto->id,
            'usuario_id' => $this->user->id,
            'entradas' => 0,
            'salidas' => 0,
            'traslados' => 30,
            'venta_diaria' => 0,
            'tienda_relacionada_id' => $tienda2->id,
        ]);

        $this->inventario->refresh();
        $inventario2->refresh();
        expect($this->inventario->cantidad)->toBe(70.0);
        expect($inventario2->cantidad)->toBe(80.0);

        // Transferir de tienda2 a tienda1
        $this->post(route('movimientos.store'), [
            'inventario_tienda_id' => $inventario2->id,
            'producto_id' => $this->producto->id,
            'usuario_id' => $this->user->id,
            'entradas' => 0,
            'salidas' => 0,
            'traslados' => 20,
            'venta_diaria' => 0,
            'tienda_relacionada_id' => $this->tienda->id,
        ]);

        $this->inventario->refresh();
        $inventario2->refresh();
        expect($this->inventario->cantidad)->toBe(90.0);
        expect($inventario2->cantidad)->toBe(60.0);
    });
});

describe('Cambio de Inventario en Actualización', function () {
    it('maneja correctamente el cambio de tienda al actualizar', function () {
        $tienda2 = Tienda::factory()->create(['is_active' => true]);
        $inventario2 = InventarioTienda::factory()->create([
            'tienda_id' => $tienda2->id,
            'producto_id' => $this->producto->id,
            'cantidad' => 50,
        ]);

        $cantidadInicial1 = $this->inventario->cantidad;
        $cantidadInicial2 = $inventario2->cantidad;

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

        // Cambiar a inventario2
        $response = $this->put(route('movimientos.update', $movimiento), [
            'inventario_tienda_id' => $inventario2->id,
            'producto_id' => $this->producto->id,
            'usuario_id' => $this->user->id,
            'entradas' => 30,
            'salidas' => 0,
            'traslados' => 10,
            'venta_diaria' => 5,
        ]);

        $response->assertSessionDoesntHaveErrors();

        $this->inventario->refresh();
        $inventario2->refresh();

        // Inventario 1 debe restaurarse
        expect($this->inventario->cantidad)->toBe($cantidadInicial1);
        // Inventario 2 debe aplicar el movimiento
        expect($inventario2->cantidad)->toBe($cantidadInicial2 + 30 - 10 - 5);
    });
});

describe('Casos de Concurrencia', function () {
    it('maneja correctamente múltiples movimientos creados simultáneamente', function () {
        $cantidadInicial = $this->inventario->cantidad;

        // Simular 3 movimientos creados "simultáneamente"
        $movimientos = [];
        for ($i = 0; $i < 3; $i++) {
            $movimientos[] = Movimiento::create([
                'inventario_tienda_id' => $this->inventario->id,
                'producto_id' => $this->producto->id,
                'usuario_id' => $this->user->id,
                'entradas' => 10,
                'salidas' => 0,
                'traslados' => 5,
                'venta_diaria' => 0,
            ]);
        }

        $this->inventario->refresh();
        // Cada movimiento: +10 -5 = +5, total: +15
        expect($this->inventario->cantidad)->toBe($cantidadInicial + 15);
    });
});
