<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('reportes_contingencia')) {
            Schema::create('reportes_contingencia', function (Blueprint $table) {
                $table->id();
                $table->string('tipo_evento', 100);
                $table->decimal('temperatura_pico', 5, 2);
                $table->integer('duracion_segundos')->nullable();
                $table->text('diagnostico_ia')->nullable();
                $table->text('recomendacion_ia')->nullable();
                $table->timestamp('fecha_siniestro')->nullable()->useCurrent();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('reportes_contingencia');
    }
};