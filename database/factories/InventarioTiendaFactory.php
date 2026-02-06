<?php

namespace Database\Factories;

use App\Models\InventarioTienda;
use App\Models\Producto;
use App\Models\Tienda;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\InventarioTienda>
 */
class InventarioTiendaFactory extends Factory
{
    protected $model = InventarioTienda::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tienda_id' => Tienda::factory(),
            'producto_id' => Producto::factory(),
            'cantidad' => fake()->numberBetween(0, 500),
            'cantidad_minima' => fake()->numberBetween(0, 50),
            'cantidad_maxima' => fake()->numberBetween(100, 1000),
        ];
    }
}
