<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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

    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }

    public function destino()
    {
        return $this->belongsTo(Destino::class);
    }

    public function usuario()
    {
        return $this->belongsTo(User::class);
    }

    public function inventario_tienda()
    {
        return $this->belongsTo(InventarioTienda::class);
    }
}
