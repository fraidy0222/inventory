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
        Schema::create('inventario_tiendas', function (Blueprint $table) {
            $table->id();
            $table->decimal('cantidad', total: 10, places: 2);
            $table->decimal('cantidad_minima', total: 10, places: 2);
            $table->decimal('cantidad_maxima', total: 10, places: 2);
            $table->dateTime('ultima_actualizacion')->useCurrent();
            $table->timestamps();

            $table->foreignId('producto_id')->constrained('productos');
            $table->foreignId('tienda_id')->constrained('tiendas');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventario_tiendas');
    }
};
