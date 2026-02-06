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

describe('MovimientoStoreRequest Validation', function () {
    it('requiere inventario_tienda_id', function () {
        $response = $this->post(route('movimientos.store'), [
            'producto_id' => $this->producto->id,
            'usuario_id' => $this->user->id,
            'entradas' => 10,
            'salidas' => 0,
            'traslados' => 0,
            'venta_diaria' => 0,
        ]);

        $response->assertSessionHasErrors('inventario_tienda_id');
    });

    it('requiere producto_id', function () {
        $response = $this->post(route('movimientos.store'), [
            'inventario_tienda_id' => $this->inventario->id,
            'usuario_id' => $this->user->id,
            'entradas' => 10,
            'salidas' => 0,
            'traslados' => 0,
            'venta_diaria' => 0,
        ]);

        $response->assertSessionHasErrors('producto_id');
    });

    it('requiere usuario_id', function () {
        $response = $this->post(route('movimientos.store'), [
            'inventario_tienda_id' => $this->inventario->id,
            'producto_id' => $this->producto->id,
            'entradas' => 10,
            'salidas' => 0,
            'traslados' => 0,
            'venta_diaria' => 0,
        ]);

        $response->assertSessionHasErrors('usuario_id');
    });

    it('valida que inventario_tienda_id existe', function () {
        $response = $this->post(route('movimientos.store'), [
            'inventario_tienda_id' => 99999,
            'producto_id' => $this->producto->id,
            'usuario_id' => $this->user->id,
            'entradas' => 10,
            'salidas' => 0,
            'traslados' => 0,
            'venta_diaria' => 0,
        ]);

        $response->assertSessionHasErrors('inventario_tienda_id');
    });

    it('valida que producto_id existe', function () {
        $response = $this->post(route('movimientos.store'), [
            'inventario_tienda_id' => $this->inventario->id,
            'producto_id' => 99999,
            'usuario_id' => $this->user->id,
            'entradas' => 10,
            'salidas' => 0,
            'traslados' => 0,
            'venta_diaria' => 0,
        ]);

        $response->assertSessionHasErrors('producto_id');
    });

    it('valida que usuario_id existe', function () {
        $response = $this->post(route('movimientos.store'), [
            'inventario_tienda_id' => $this->inventario->id,
            'producto_id' => $this->producto->id,
            'usuario_id' => 99999,
            'entradas' => 10,
            'salidas' => 0,
            'traslados' => 0,
            'venta_diaria' => 0,
        ]);

        $response->assertSessionHasErrors('usuario_id');
    });

    it('valida que destino_id existe cuando se proporciona', function () {
        $response = $this->post(route('movimientos.store'), [
            'inventario_tienda_id' => $this->inventario->id,
            'producto_id' => $this->producto->id,
            'usuario_id' => $this->user->id,
            'destino_id' => 99999,
            'entradas' => 10,
            'salidas' => 0,
            'traslados' => 0,
            'venta_diaria' => 0,
        ]);

        $response->assertSessionHasErrors('destino_id');
    });

    it('valida que tienda_relacionada_id existe cuando se proporciona', function () {
        $response = $this->post(route('movimientos.store'), [
            'inventario_tienda_id' => $this->inventario->id,
            'producto_id' => $this->producto->id,
            'usuario_id' => $this->user->id,
            'tienda_relacionada_id' => 99999,
            'entradas' => 10,
            'salidas' => 0,
            'traslados' => 0,
            'venta_diaria' => 0,
        ]);

        $response->assertSessionHasErrors('tienda_relacionada_id');
    });

    it('valida que entradas no sea negativa', function () {
        $response = $this->post(route('movimientos.store'), [
            'inventario_tienda_id' => $this->inventario->id,
            'producto_id' => $this->producto->id,
            'usuario_id' => $this->user->id,
            'entradas' => -10,
            'salidas' => 0,
            'traslados' => 0,
            'venta_diaria' => 0,
        ]);

        $response->assertSessionHasErrors('entradas');
    });

    it('valida que salidas no sea negativa', function () {
        $response = $this->post(route('movimientos.store'), [
            'inventario_tienda_id' => $this->inventario->id,
            'producto_id' => $this->producto->id,
            'usuario_id' => $this->user->id,
            'entradas' => 0,
            'salidas' => -10,
            'traslados' => 0,
            'venta_diaria' => 0,
        ]);

        $response->assertSessionHasErrors('salidas');
    });

    it('valida que traslados no sea negativa', function () {
        $response = $this->post(route('movimientos.store'), [
            'inventario_tienda_id' => $this->inventario->id,
            'producto_id' => $this->producto->id,
            'usuario_id' => $this->user->id,
            'entradas' => 0,
            'salidas' => 0,
            'traslados' => -10,
            'venta_diaria' => 0,
        ]);

        $response->assertSessionHasErrors('traslados');
    });

    it('valida que venta_diaria no sea negativa', function () {
        $response = $this->post(route('movimientos.store'), [
            'inventario_tienda_id' => $this->inventario->id,
            'producto_id' => $this->producto->id,
            'usuario_id' => $this->user->id,
            'entradas' => 0,
            'salidas' => 0,
            'traslados' => 0,
            'venta_diaria' => -10,
        ]);

        $response->assertSessionHasErrors('venta_diaria');
    });

    it('valida que traslados + venta_diaria no exceda el stock disponible', function () {
        $response = $this->post(route('movimientos.store'), [
            'inventario_tienda_id' => $this->inventario->id,
            'producto_id' => $this->producto->id,
            'usuario_id' => $this->user->id,
            'entradas' => 0,
            'salidas' => 0,
            'traslados' => 60,
            'venta_diaria' => 50, // Total: 110, disponible: 100
        ]);

        $response->assertSessionHasErrors('inventario_tienda_id');
    });

    it('permite crear movimiento cuando traslados + venta_diaria es igual al stock disponible', function () {
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
        $response->assertRedirect(route('movimientos.index'));
    });

    it('permite crear movimiento cuando traslados + venta_diaria es menor al stock disponible', function () {
        $response = $this->post(route('movimientos.store'), [
            'inventario_tienda_id' => $this->inventario->id,
            'producto_id' => $this->producto->id,
            'usuario_id' => $this->user->id,
            'entradas' => 0,
            'salidas' => 0,
            'traslados' => 30,
            'venta_diaria' => 20, // Total: 50, disponible: 100
        ]);

        $response->assertSessionDoesntHaveErrors();
        $response->assertRedirect(route('movimientos.index'));
    });
});

describe('MovimientoUpdateRequest Validation', function () {
    beforeEach(function () {
        $this->movimiento = Movimiento::create([
            'inventario_tienda_id' => $this->inventario->id,
            'producto_id' => $this->producto->id,
            'usuario_id' => $this->user->id,
            'entradas' => 0,
            'salidas' => 0,
            'traslados' => 20,
            'venta_diaria' => 10,
        ]);
    });

    it('requiere inventario_tienda_id', function () {
        $response = $this->put(route('movimientos.update', $this->movimiento), [
            'producto_id' => $this->producto->id,
            'usuario_id' => $this->user->id,
            'entradas' => 10,
            'salidas' => 0,
            'traslados' => 0,
            'venta_diaria' => 0,
        ]);

        $response->assertSessionHasErrors('inventario_tienda_id');
    });

    it('requiere producto_id', function () {
        $response = $this->put(route('movimientos.update', $this->movimiento), [
            'inventario_tienda_id' => $this->inventario->id,
            'usuario_id' => $this->user->id,
            'entradas' => 10,
            'salidas' => 0,
            'traslados' => 0,
            'venta_diaria' => 0,
        ]);

        $response->assertSessionHasErrors('producto_id');
    });

    it('requiere usuario_id', function () {
        $response = $this->put(route('movimientos.update', $this->movimiento), [
            'inventario_tienda_id' => $this->inventario->id,
            'producto_id' => $this->producto->id,
            'entradas' => 10,
            'salidas' => 0,
            'traslados' => 0,
            'venta_diaria' => 0,
        ]);

        $response->assertSessionHasErrors('usuario_id');
    });

    it('considera el stock consumido por el movimiento original al validar', function () {
        // El movimiento original consume 30 (20 traslados + 10 venta_diaria)
        // Stock actual: 100 - 30 = 70
        // Al editar, debería poder usar hasta 100 (70 actual + 30 del movimiento original)

        $response = $this->put(route('movimientos.update', $this->movimiento), [
            'inventario_tienda_id' => $this->inventario->id,
            'producto_id' => $this->producto->id,
            'usuario_id' => $this->user->id,
            'entradas' => 0,
            'salidas' => 0,
            'traslados' => 60,
            'venta_diaria' => 40, // Total: 100
        ]);

        $response->assertSessionDoesntHaveErrors();
    });

    it('valida que no exceda el stock disponible incluyendo el movimiento original', function () {
        // El movimiento original consume 30
        // Stock actual: 70
        // Disponible para edición: 100
        // Intentar usar 110 debería fallar

        $response = $this->put(route('movimientos.update', $this->movimiento), [
            'inventario_tienda_id' => $this->inventario->id,
            'producto_id' => $this->producto->id,
            'usuario_id' => $this->user->id,
            'entradas' => 0,
            'salidas' => 0,
            'traslados' => 70,
            'venta_diaria' => 40, // Total: 110
        ]);

        $response->assertSessionHasErrors('inventario_tienda_id');
    });

    it('valida correctamente al cambiar de inventario', function () {
        $tienda2 = Tienda::factory()->create(['is_active' => true]);
        $inventario2 = InventarioTienda::factory()->create([
            'tienda_id' => $tienda2->id,
            'producto_id' => $this->producto->id,
            'cantidad' => 50,
        ]);

        // Al cambiar a otro inventario, debe validar contra el stock de ese inventario
        $response = $this->put(route('movimientos.update', $this->movimiento), [
            'inventario_tienda_id' => $inventario2->id,
            'producto_id' => $this->producto->id,
            'usuario_id' => $this->user->id,
            'entradas' => 0,
            'salidas' => 0,
            'traslados' => 30,
            'venta_diaria' => 25, // Total: 55, disponible en inventario2: 50
        ]);

        $response->assertSessionHasErrors('inventario_tienda_id');
    });
});

describe('Validation with Decimal Values', function () {
    it('acepta valores decimales válidos', function () {
        $response = $this->post(route('movimientos.store'), [
            'inventario_tienda_id' => $this->inventario->id,
            'producto_id' => $this->producto->id,
            'usuario_id' => $this->user->id,
            'entradas' => 25.5,
            'salidas' => 0,
            'traslados' => 10.25,
            'venta_diaria' => 5.75,
        ]);

        $response->assertSessionDoesntHaveErrors();
    });

    it('valida correctamente con valores decimales que exceden el stock', function () {
        $response = $this->post(route('movimientos.store'), [
            'inventario_tienda_id' => $this->inventario->id,
            'producto_id' => $this->producto->id,
            'usuario_id' => $this->user->id,
            'entradas' => 0,
            'salidas' => 0,
            'traslados' => 60.5,
            'venta_diaria' => 40.5, // Total: 101, disponible: 100
        ]);

        $response->assertSessionHasErrors('inventario_tienda_id');
    });
});
