<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('decisiones_ia', function (Blueprint $table) {
            $table->id();

            $table->timestamp('fecha_hora')->useCurrent();

            $table->enum('nivel_riesgo', ['normal', 'advertencia', 'critico']);

            $table->decimal('temperatura', 5, 2);
            $table->decimal('humedad', 5, 2);

            $table->string('accion_ejecutada')->nullable();
            $table->text('motivo');

            $table->boolean('ejecutada_automaticamente')->default(false);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('decisiones_ia');
    }
};