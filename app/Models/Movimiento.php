<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Movimiento extends Model
{
    protected $fillable = [
        'entradas',
        'salidas',
        'traslados',
        'venta_diaria',
        'producto_id',
        'destino_id',
        'usuario_id',
        'inventario_tienda_id',
    ];

    protected $appends = ['inventario_actual'];

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class);
    }

    public function destino(): BelongsTo
    {
        return $this->belongsTo(Destino::class);
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function inventario_tienda(): BelongsTo
    {
        return $this->belongsTo(InventarioTienda::class);
    }

    // public function getInventarioActualAttribute(): int
    // {
    //     if (!$this->inventario_tienda) {
    //         return 0;
    //     }

    //     return $this->inventario_tienda->cantidad - $this->traslados - $this->venta_diaria;
    // }

    protected static function booted()
    {
        static::created(function ($movimiento) {
            $diferencia = - ($movimiento->traslados + $movimiento->venta_diaria);
            $movimiento->inventario_tienda->cantidad += $diferencia;
            $movimiento->inventario_tienda->save();
        });

        static::updated(function ($movimiento) {

            // Si necesitas manejar actualizaciones, calcula la diferencia
            $diferenciaOriginal = $movimiento->getOriginal('traslados') + $movimiento->getOriginal('venta_diaria');
            $diferenciaNueva = $movimiento->traslados + $movimiento->venta_diaria;
            $diferencia = $diferenciaOriginal - $diferenciaNueva;

            $movimiento->inventario_tienda->cantidad += $diferencia;
            $movimiento->inventario_tienda->save();
        });

        static::deleted(function ($movimiento) {
            // Restaurar la cantidad si se elimina el movimiento
            $diferencia = $movimiento->traslados + $movimiento->venta_diaria;
            $movimiento->inventario_tienda->cantidad += $diferencia;
            $movimiento->inventario_tienda->save();
        });
    }
}
