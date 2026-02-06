<?php

namespace Database\Factories;

use App\Models\Producto;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Producto>
 */
class ProductoFactory extends Factory
{
    protected $model = Producto::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nombre' => fake()->words(3, true),
            'categoria' => fake()->randomElement(['Electrónica', 'Ropa', 'Alimentos', 'Hogar', 'Deportes']),
            'activo' => true,
        ];
    }

    /**
     * Indicate that the producto is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn(array $attributes) => [
            'activo' => false,
        ]);
    }
}
