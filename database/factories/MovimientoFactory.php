<?php

namespace Database\Factories;

use App\Models\Destino;
use App\Models\InventarioTienda;
use App\Models\Movimiento;
use App\Models\Producto;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Movimiento>
 */
class MovimientoFactory extends Factory
{
    protected $model = Movimiento::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'inventario_tienda_id' => InventarioTienda::factory(),
            'producto_id' => Producto::factory(),
            'usuario_id' => User::factory(),
            'destino_id' => Destino::factory(),
            'entradas' => fake()->randomFloat(2, 0, 100),
            'salidas' => 0,
            'traslados' => fake()->randomFloat(2, 0, 50),
            'venta_diaria' => fake()->randomFloat(2, 0, 50),
            'tienda_relacionada_id' => null,
        ];
    }

    /**
     * Indicate that the movimiento is an entrada (entry).
     */
    public function entrada(): static
    {
        return $this->state(fn(array $attributes) => [
            'entradas' => fake()->randomFloat(2, 10, 100),
            'salidas' => 0,
            'traslados' => 0,
            'venta_diaria' => 0,
        ]);
    }

    /**
     * Indicate that the movimiento is a traslado (transfer).
     */
    public function traslado(): static
    {
        return $this->state(fn(array $attributes) => [
            'entradas' => 0,
            'salidas' => 0,
            'traslados' => fake()->randomFloat(2, 10, 50),
            'venta_diaria' => 0,
        ]);
    }

    /**
     * Indicate that the movimiento is a venta (sale).
     */
    public function venta(): static
    {
        return $this->state(fn(array $attributes) => [
            'entradas' => 0,
            'salidas' => 0,
            'traslados' => 0,
            'venta_diaria' => fake()->randomFloat(2, 10, 50),
        ]);
    }
}
