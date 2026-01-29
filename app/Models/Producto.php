<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Producto extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',
        'descripcion',
        'categoria',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    public function tiendas(): BelongsToMany
    {
        return $this->belongsToMany(Tienda::class, 'inventario_tiendas')
            ->withPivot('cantidad', 'cantidad_minima', 'cantidad_maxima', 'ultima_actualizacion')
            ->withTimestamps();
    }

    public function inventario(): HasMany
    {
        return $this->hasMany(InventarioTienda::class);
    }

    public function inventarioTienda(): HasMany
    {
        return $this->hasMany(InventarioTienda::class);
    }

    public function movimientos(): HasMany
    {
        return $this->hasMany(Movimiento::class);
    }
}
