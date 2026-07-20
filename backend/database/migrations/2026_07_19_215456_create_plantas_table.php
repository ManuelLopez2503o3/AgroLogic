<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plantas', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->decimal('humedad_min', 5, 2);
            $table->decimal('humedad_max', 5, 2);
            $table->decimal('temperatura_min', 5, 2);
            $table->decimal('temperatura_max', 5, 2);
            $table->text('nutrientes_recomendados');
            $table->text('descripcion')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plantas');
    }
};