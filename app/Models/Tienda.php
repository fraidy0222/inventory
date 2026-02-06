<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Tienda extends Model
{
    use HasFactory;
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'nombre',
        'is_active'
    ];


    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function productos(): BelongsToMany
    {
        return $this->belongsToMany(Producto::class, 'inventario_tiendas')
            ->withPivot('cantidad', 'cantidad_minima', 'cantidad_maxima', 'ultima_actualizacion')
            ->withTimestamps();
    }

    public function inventarioTienda(): HasMany
    {
        return $this->hasMany(InventarioTienda::class);
    }
}
