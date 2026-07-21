<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('telemetrias')) {
            Schema::create('telemetrias', function (Blueprint $table) {
                $table->id();
                $table->decimal('temperatura', 5, 2);
                $table->decimal('humedad', 5, 2);
                $table->enum('estado', ['normal', 'alerta', 'modo_fuego'])->default('normal');
                $table->timestamp('fecha_registro')->nullable()->useCurrent();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('telemetrias');
    }
};