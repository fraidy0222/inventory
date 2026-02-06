<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Destino extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',
    ];

    public function movimientos(): HasMany
    {
        return $this->hasMany(Movimiento::class);
    }
}
