<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('incidentes_fuego', function (Blueprint $table) {
            $table->id();
            $table->timestamp('fecha_inicio');
            $table->timestamp('fecha_fin')->nullable();
            $table->float('pico_temperatura');
            $table->float('humedad_final')->nullable();
            $table->integer('duracion_segundos')->nullable();
            $table->text('reporte_ia')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('incidentes_fuego');
    }
};