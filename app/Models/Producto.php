<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Producto extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',
        'descripcion',
        'categoria',
        'costo_promedio',
        'precio_venta',
        'activo',
    ];

    protected $casts = [
        'costo_promedio' => 'decimal:2',
        'precio_venta' => 'decimal:2',
        'activo' => 'boolean',
    ];

    public function inventarioTiendas(): HasMany
    {
        return $this->hasMany(InventarioTienda::class);
    }
}
