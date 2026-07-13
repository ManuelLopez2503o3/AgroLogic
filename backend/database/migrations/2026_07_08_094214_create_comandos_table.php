<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('comandos', function (Blueprint $table) {
            $table->id();
            $table->string('modo');            // "manual" o "auto"
            $table->string('color')->nullable();
            $table->float('umbral_alto')->nullable();
            $table->float('umbral_bajo')->nullable();
            $table->boolean('enviado')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('comandos');
    }
};