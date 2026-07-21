<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('reportes_salud')) {
            Schema::create('reportes_salud', function (Blueprint $table) {
                $table->id();
                $table->date('fecha')->unique();
                $table->decimal('temperatura_promedio', 5, 2);
                $table->decimal('temperatura_min', 5, 2);
                $table->decimal('temperatura_max', 5, 2);
                $table->decimal('humedad_promedio', 5, 2);
                $table->text('diagnostico_ia')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('reportes_salud');
    }
};