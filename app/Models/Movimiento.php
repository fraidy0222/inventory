<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Movimiento extends Model
{
    use HasFactory;

    protected $fillable = [
        'existencias',
        'entradas',
        'salidas',
        'traslados',
        'venta_diaria',
        'producto_id',
        'destino_id',
        'usuario_id',
        'inventario_tienda_id',
        'tienda_relacionada_id',
    ];

    // protected $appends = ['inventario_actual']; // Removed - no accessor defined

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

    public function tienda_relacionada(): BelongsTo
    {
        return $this->belongsTo(Tienda::class, 'tienda_relacionada_id');
    }

    protected static function booted()
    {
        static::created(function ($movimiento) {
            $totalEntrada = $movimiento->entradas;
            $totalSalida = $movimiento->traslados + $movimiento->venta_diaria;
            $diferencia = $totalEntrada - $totalSalida;

            $movimiento->inventario_tienda->cantidad += $diferencia;
            $movimiento->inventario_tienda->save();
        });

        static::updated(function ($movimiento) {
            if ($movimiento->isDirty('inventario_tienda_id')) {
                // 1. Restaurar cantidad al inventario anterior
                $originalInventarioId = $movimiento->getOriginal('inventario_tienda_id');
                $originalEntrada = $movimiento->getOriginal('entradas');
                $originalSalida = $movimiento->getOriginal('traslados') + $movimiento->getOriginal('venta_diaria');
                $originalNeto = $originalEntrada - $originalSalida;

                $oldInventario = InventarioTienda::find($originalInventarioId);
                if ($oldInventario) {
                    $oldInventario->cantidad -= $originalNeto; // Remove the effect of the old movement
                    $oldInventario->save();
                }

                // 2. Aplicar al nuevo inventario
                // Nota: $movimiento->inventario_tienda ya apunta al nuevo registro debido al cambio de ID
                $nuevoEntrada = $movimiento->entradas;
                $nuevoSalida = $movimiento->traslados + $movimiento->venta_diaria;
                $nuevoNeto = $nuevoEntrada - $nuevoSalida;

                $movimiento->inventario_tienda->cantidad += $nuevoNeto;
                $movimiento->inventario_tienda->save();
            } else {
                // Lógica estándar cuando no cambia el inventario
                $originalEntrada = $movimiento->getOriginal('entradas');
                $originalSalida = $movimiento->getOriginal('traslados') + $movimiento->getOriginal('venta_diaria');
                $originalNeto = $originalEntrada - $originalSalida;

                $nuevoEntrada = $movimiento->entradas;
                $nuevoSalida = $movimiento->traslados + $movimiento->venta_diaria;
                $nuevoNeto = $nuevoEntrada - $nuevoSalida;

                $diferencia = $nuevoNeto - $originalNeto;

                $movimiento->inventario_tienda->cantidad += $diferencia;
                $movimiento->inventario_tienda->save();
            }
        });

        static::deleted(function ($movimiento) {
            // Restaurar la cantidad si se elimina el movimiento
            $totalEntrada = $movimiento->entradas;
            $totalSalida = $movimiento->traslados + $movimiento->venta_diaria;
            $neto = $totalEntrada - $totalSalida;

            $movimiento->inventario_tienda->cantidad -= $neto; // Reverse effect
            $movimiento->inventario_tienda->save();
        });
    }
}
