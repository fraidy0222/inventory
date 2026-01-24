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
            $table->integer('cantidad');
            $table->integer('cantidad_minima');
            $table->integer('cantidad_maxima');
            $table->dateTime('ultima_actualizacion')->useCurrent();
            $table->timestamps();

            $table->foreignId('producto_id')->constrained('productos')->onDelete('cascade');
            $table->foreignId('tienda_id')->constrained('tiendas')->onDelete('cascade');
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
