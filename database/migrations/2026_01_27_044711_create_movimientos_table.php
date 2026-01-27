<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('movimientos', function (Blueprint $table) {
            $table->id();
            $table->integer('entradas');
            $table->integer('salidas');
            $table->integer('traslados');
            $table->integer('venta_diaria');
            $table->timestamps();

            $table->foreignId('producto_id')->constrained('productos')->cascadeOnDelete();
            $table->foreignId('destino_id')->constrained('destinos')->cascadeOnDelete();
            $table->foreignId('usuario_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('inventario_tienda_id')->constrained('inventario_tiendas')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('movimientos');
    }
};
