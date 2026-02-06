<?php

use App\Models\Destino;
use App\Models\InventarioTienda;
use App\Models\Movimiento;
use App\Models\Producto;
use App\Models\Tienda;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;

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

describe('MovimientoController Index', function () {
    it('renderiza la vista index con movimientos paginados', function () {
        Movimiento::factory()->count(5)->create([
            'inventario_tienda_id' => $this->inventario->id,
            'producto_id' => $this->producto->id,
            'usuario_id' => $this->user->id,
        ]);

        $response = $this->get(route('movimientos.index'));

        $response->assertStatus(200);
        $response->assertInertia(
            fn(Assert $page) => $page
                ->component('movimientos/index')
                ->has('movimientos')
                ->has('movimientos.data')
                ->has('filters')
        );
    });

    it('filtra movimientos por búsqueda de producto', function () {
        $producto2 = Producto::factory()->create(['nombre' => 'Producto Especial', 'activo' => true]);
        $inventario2 = InventarioTienda::factory()->create([
            'tienda_id' => $this->tienda->id,
            'producto_id' => $producto2->id,
            'cantidad' => 50,
        ]);

        Movimiento::factory()->create([
            'inventario_tienda_id' => $this->inventario->id,
            'producto_id' => $this->producto->id,
            'usuario_id' => $this->user->id,
        ]);

        Movimiento::factory()->create([
            'inventario_tienda_id' => $inventario2->id,
            'producto_id' => $producto2->id,
            'usuario_id' => $this->user->id,
        ]);

        $response = $this->get(route('movimientos.index', ['search' => 'Especial']));

        $response->assertStatus(200);
        $response->assertInertia(
            fn(Assert $page) => $page
                ->where('filters.search', 'Especial')
        );
    });

    it('filtra movimientos por tienda_id', function () {
        $tienda2 = Tienda::factory()->create(['is_active' => true]);
        $inventario2 = InventarioTienda::factory()->create([
            'tienda_id' => $tienda2->id,
            'producto_id' => $this->producto->id,
            'cantidad' => 50,
        ]);

        Movimiento::factory()->create([
            'inventario_tienda_id' => $this->inventario->id,
            'producto_id' => $this->producto->id,
            'usuario_id' => $this->user->id,
        ]);

        Movimiento::factory()->create([
            'inventario_tienda_id' => $inventario2->id,
            'producto_id' => $this->producto->id,
            'usuario_id' => $this->user->id,
        ]);

        $response = $this->get(route('movimientos.index', ['tienda_id' => $this->tienda->id]));

        $response->assertStatus(200);
        $response->assertInertia(
            fn(Assert $page) => $page
                ->where('filters.tienda_id', $this->tienda->id)
        );
    });

    it('maneja paginación con per_page', function () {
        Movimiento::factory()->count(15)->create([
            'inventario_tienda_id' => $this->inventario->id,
            'producto_id' => $this->producto->id,
            'usuario_id' => $this->user->id,
        ]);

        $response = $this->get(route('movimientos.index', ['per_page' => 5]));

        $response->assertStatus(200);
        $response->assertInertia(
            fn(Assert $page) => $page
                ->where('movimientos.per_page', 5)
        );
    });

    it('maneja per_page=-1 para mostrar todos los registros', function () {
        Movimiento::factory()->count(15)->create([
            'inventario_tienda_id' => $this->inventario->id,
            'producto_id' => $this->producto->id,
            'usuario_id' => $this->user->id,
        ]);

        $response = $this->get(route('movimientos.index', ['per_page' => -1]));

        $response->assertStatus(200);
        $response->assertInertia(
            fn(Assert $page) => $page
                ->where('movimientos.total', 15)
        );
    });
});

describe('MovimientoController Create', function () {
    it('renderiza la vista create con datos necesarios', function () {
        $response = $this->get(route('movimientos.create'));

        $response->assertStatus(200);
        $response->assertInertia(
            fn(Assert $page) => $page
                ->component('movimientos/create')
                ->has('tiendas')
                ->has('productos')
                ->has('destinos')
                ->has('usuarios')
                ->has('auth.user')
        );
    });

    it('solo muestra tiendas activas en el formulario de creación', function () {
        // Crear tiendas (sin importar cuántas ya existan)
        $tiendaInactiva = Tienda::factory()->create(['is_active' => false]);

        $response = $this->get(route('movimientos.create'));

        $response->assertInertia(
            fn(Assert $page) => $page
                ->component('movimientos/create')
                ->has('tiendas')
                ->where('tiendas', function ($tiendas) use ($tiendaInactiva) {
                    // Solo verificamos que la tienda inactiva NO está
                    $ids = collect($tiendas)->pluck('id');
                    return !$ids->contains($tiendaInactiva->id);
                })
        );
    });

    it('solo muestra productos activos en el formulario de creación', function () {
        // Crear tiendas (sin importar cuántas ya existan)
        $productoInactivo = Producto::factory()->create(['activo' => false]);

        $response = $this->get(route('movimientos.create'));

        $response->assertInertia(
            fn(Assert $page) => $page
                ->component('movimientos/create')
                ->has('productos')
                ->where('productos', function ($productos) use ($productoInactivo) {
                    // Solo verificamos que la tienda inactiva NO está
                    $ids = collect($productos)->pluck('id');
                    return !$ids->contains($productoInactivo->id);
                })
        );
    });
});

describe('MovimientoController Store', function () {
    it('crea un movimiento simple correctamente', function () {
        $cantidadInicial = $this->inventario->cantidad;

        $response = $this->post(route('movimientos.store'), [
            'inventario_tienda_id' => $this->inventario->id,
            'producto_id' => $this->producto->id,
            'usuario_id' => $this->user->id,
            'destino_id' => $this->destino->id,
            'entradas' => 50,
            'salidas' => 0,
            'traslados' => 10,
            'venta_diaria' => 5,
        ]);

        $response->assertRedirect(route('movimientos.index'));

        $this->assertDatabaseHas('movimientos', [
            'producto_id' => $this->producto->id,
            'usuario_id' => $this->user->id,
            'entradas' => 50,
            'traslados' => 10,
            'venta_diaria' => 5,
        ]);

        $this->inventario->refresh();
        expect($this->inventario->cantidad)->toBe($cantidadInicial + 50 - 10 - 5);
    });

    it('crea movimiento de transferencia entre tiendas', function () {
        $tienda2 = Tienda::factory()->create(['is_active' => true]);

        $response = $this->post(route('movimientos.store'), [
            'inventario_tienda_id' => $this->inventario->id,
            'producto_id' => $this->producto->id,
            'usuario_id' => $this->user->id,
            'entradas' => 0,
            'salidas' => 0,
            'traslados' => 30,
            'venta_diaria' => 0,
            'tienda_relacionada_id' => $tienda2->id,
        ]);

        $response->assertRedirect(route('movimientos.index'));

        // Verificar que se creó el movimiento de salida
        $this->assertDatabaseHas('movimientos', [
            'inventario_tienda_id' => $this->inventario->id,
            'traslados' => 30,
            'tienda_relacionada_id' => $tienda2->id,
        ]);

        // Verificar que se creó el inventario en la tienda destino
        $inventarioDestino = InventarioTienda::where('tienda_id', $tienda2->id)
            ->where('producto_id', $this->producto->id)
            ->first();

        expect($inventarioDestino)->not->toBeNull();

        // Verificar que se creó el movimiento de entrada en la tienda destino
        $this->assertDatabaseHas('movimientos', [
            'inventario_tienda_id' => $inventarioDestino->id,
            'entradas' => 30,
            'tienda_relacionada_id' => $this->tienda->id,
        ]);
    });
});

describe('MovimientoController Edit', function () {
    it('renderiza la vista edit con datos del movimiento', function () {
        $movimiento = Movimiento::factory()->create([
            'inventario_tienda_id' => $this->inventario->id,
            'producto_id' => $this->producto->id,
            'usuario_id' => $this->user->id,
            'destino_id' => $this->destino->id,
        ]);

        $response = $this->get(route('movimientos.edit', $movimiento));

        $response->assertStatus(200);
        $response->assertInertia(
            fn(Assert $page) => $page
                ->component('movimientos/edit')
                ->has('movimiento')
                ->has('tiendas')
                ->has('productos')
                ->has('destinos')
                ->has('usuarios')
                ->has('inventariosTienda')
                ->where('movimiento.id', $movimiento->id)
        );
    });
});

describe('MovimientoController Update', function () {
    it('actualiza un movimiento correctamente', function () {
        $movimiento = Movimiento::factory()->create([
            'inventario_tienda_id' => $this->inventario->id,
            'producto_id' => $this->producto->id,
            'usuario_id' => $this->user->id,
            'entradas' => 50,
            'traslados' => 10,
            'venta_diaria' => 5,
        ]);

        $response = $this->put(route('movimientos.update', $movimiento), [
            'inventario_tienda_id' => $this->inventario->id,
            'producto_id' => $this->producto->id,
            'usuario_id' => $this->user->id,
            'entradas' => 80,
            'salidas' => 0,
            'traslados' => 20,
            'venta_diaria' => 10,
        ]);

        $response->assertRedirect(route('movimientos.index'));

        $movimiento->refresh();
        expect($movimiento->entradas)->toBe(80);
        expect($movimiento->traslados)->toBe(20);
        expect($movimiento->venta_diaria)->toBe(10);
    });
});

describe('MovimientoController Destroy', function () {
    it('elimina un movimiento correctamente', function () {
        $cantidadInicial = $this->inventario->cantidad;

        $movimiento = Movimiento::factory()->create([
            'inventario_tienda_id' => $this->inventario->id,
            'producto_id' => $this->producto->id,
            'usuario_id' => $this->user->id,
            'entradas' => 50,
            'traslados' => 10,
            'venta_diaria' => 5,
        ]);

        $this->inventario->refresh();
        $cantidadDespuesCrear = $this->inventario->cantidad;

        $response = $this->delete(route('movimientos.destroy', $movimiento));

        $response->assertRedirect(route('movimientos.index'));

        $this->assertDatabaseMissing('movimientos', [
            'id' => $movimiento->id,
        ]);

        $this->inventario->refresh();
        expect($this->inventario->cantidad)->toBe($cantidadInicial);
    });
});

describe('MovimientoController getInventarioTiendaId', function () {
    it('retorna inventario correcto para tienda y producto', function () {
        $response = $this->get("/api/movimientos/inventario/{$this->tienda->id}/{$this->producto->id}");

        $response->assertStatus(200);
        $response->assertJson([
            'id' => $this->inventario->id,
            'cantidad' => $this->inventario->cantidad,
        ]);
    });

    it('retorna error 404 si no existe inventario', function () {
        $producto2 = Producto::factory()->create(['activo' => true]);

        $response = $this->get("/api/movimientos/inventario/{$this->tienda->id}/{$producto2->id}");

        $response->assertStatus(404);
        $response->assertJson([
            'error' => 'No se encontró inventario para esta combinación de tienda y producto',
        ]);
    });
});

describe('MovimientoController getProductosEnTienda', function () {
    it('retorna solo productos que existen en el inventario de la tienda', function () {
        $producto2 = Producto::factory()->create(['activo' => true]);
        InventarioTienda::factory()->create([
            'tienda_id' => $this->tienda->id,
            'producto_id' => $producto2->id,
            'cantidad' => 50,
        ]);

        $producto3 = Producto::factory()->create(['activo' => true]); // No tiene inventario

        $response = $this->get("/api/movimientos/productos-en-tienda/{$this->tienda->id}");

        $response->assertStatus(200);
        $data = $response->json();

        expect($data)->toHaveCount(2);
        expect(collect($data)->pluck('id')->toArray())->toContain($this->producto->id);
        expect(collect($data)->pluck('id')->toArray())->toContain($producto2->id);
        expect(collect($data)->pluck('id')->toArray())->not->toContain($producto3->id);
    });
});

describe('MovimientoController checkStockAvailability', function () {
    it('retorna tiendas con stock disponible', function () {
        $tienda2 = Tienda::factory()->create(['is_active' => true]);
        $tienda3 = Tienda::factory()->create(['is_active' => true]);

        InventarioTienda::factory()->create([
            'tienda_id' => $tienda2->id,
            'producto_id' => $this->producto->id,
            'cantidad' => 50,
        ]);

        InventarioTienda::factory()->create([
            'tienda_id' => $tienda3->id,
            'producto_id' => $this->producto->id,
            'cantidad' => 0, // Sin stock
        ]);

        $response = $this->get("/api/movimientos/check-stock/{$this->producto->id}?exclude_tienda_id={$this->tienda->id}");

        $response->assertStatus(200);
        $data = $response->json();

        expect($data)->toHaveCount(1);
        expect($data[0]['tienda_id'])->toBe($tienda2->id);
        expect($data[0]['cantidad'])->toBe(50);
    });

    it('excluye la tienda actual', function () {
        $tienda2 = Tienda::factory()->create(['is_active' => true]);
        InventarioTienda::factory()->create([
            'tienda_id' => $tienda2->id,
            'producto_id' => $this->producto->id,
            'cantidad' => 50,
        ]);

        $response = $this->get("/api/movimientos/check-stock/{$this->producto->id}?exclude_tienda_id={$this->tienda->id}");

        $response->assertStatus(200);
        $data = $response->json();

        expect(collect($data)->pluck('tienda_id')->toArray())->not->toContain($this->tienda->id);
    });
});

describe('MovimientoController transferAndUse', function () {
    it('crea transferencia y uso correctamente', function () {
        $tienda2 = Tienda::factory()->create(['is_active' => true]);
        $inventario2 = InventarioTienda::factory()->create([
            'tienda_id' => $tienda2->id,
            'producto_id' => $this->producto->id,
            'cantidad' => 10,
        ]);

        $response = $this->post('/api/movimientos/transfer-and-use', [
            'source_tienda_id' => $this->tienda->id,
            'target_tienda_id' => $tienda2->id,
            'producto_id' => $this->producto->id,
            'cantidad_transferir' => 30,
            'movimiento_data' => [
                'producto_id' => $this->producto->id,
                'entradas' => 0,
                'salidas' => 0,
                'traslados' => 20,
                'venta_diaria' => 15,
            ],
        ]);

        $response->assertStatus(200);
        $response->assertJson(['message' => 'Transferencia y movimiento realizados con éxito.']);

        // Verificar movimiento de salida en tienda origen
        $this->assertDatabaseHas('movimientos', [
            'inventario_tienda_id' => $this->inventario->id,
            'traslados' => 30,
            'tienda_relacionada_id' => $tienda2->id,
        ]);

        // Verificar movimiento de entrada en tienda destino
        $this->assertDatabaseHas('movimientos', [
            'inventario_tienda_id' => $inventario2->id,
            'entradas' => 30,
            'tienda_relacionada_id' => $this->tienda->id,
        ]);

        // Verificar movimiento de uso en tienda destino
        $this->assertDatabaseHas('movimientos', [
            'inventario_tienda_id' => $inventario2->id,
            'traslados' => 20,
            'venta_diaria' => 15,
        ]);
    });

    it('valida stock insuficiente en tienda origen', function () {
        $tienda2 = Tienda::factory()->create(['is_active' => true]);

        // Asegúrate de estar autenticado
        $this->actingAs($this->user);

        $response = $this->postJson('/api/movimientos/transfer-and-use', [
            'source_tienda_id' => $this->tienda->id,
            'target_tienda_id' => $tienda2->id,
            'producto_id' => $this->producto->id,
            'cantidad_transferir' => 150, // Más de lo disponible (100)
            'movimiento_data' => [
                'inventario_tienda_id' => $this->inventario->id, // ← REQUERIDO por Movimiento::create
                'producto_id' => $this->producto->id,
                'usuario_id' => $this->user->id, // ← REQUERIDO
                'destino_id' => null,
                'entradas' => 0,
                'salidas' => 0,
                'traslados' => 0, // ← Esto es para el movimiento de USO, no para transferencia
                'venta_diaria' => 0,
                'tienda_relacionada_id' => $this->tienda->id, // ← Puede ser requerido
            ],
        ]);

        // Para debug: ver qué está pasando
        if ($response->status() !== 422) {
            dump('Status:', $response->status());
            dump('Response:', $response->json());
        }

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['cantidad_transferir']);
    });

    it('valida stock insuficiente en tienda destino después de transferencia', function () {
        $tienda2 = Tienda::factory()->create(['is_active' => true]);
        $inventario2 = InventarioTienda::factory()->create([
            'tienda_id' => $tienda2->id,
            'producto_id' => $this->producto->id,
            'cantidad' => 10,
        ]);

        $response = $this->post('/api/movimientos/transfer-and-use', [
            'source_tienda_id' => $this->tienda->id,
            'target_tienda_id' => $tienda2->id,
            'producto_id' => $this->producto->id,
            'cantidad_transferir' => 30,
            'movimiento_data' => [
                'producto_id' => $this->producto->id,
                'entradas' => 0,
                'salidas' => 0,
                'traslados' => 30,
                'venta_diaria' => 20, // Total: 50, disponible después de transferencia: 40
            ],
        ]);

        $response->assertStatus(422);
        $response->assertJsonStructure(['errors']);
    });
});
