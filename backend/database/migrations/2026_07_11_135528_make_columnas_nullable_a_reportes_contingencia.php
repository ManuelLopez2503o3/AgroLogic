<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reportes_contingencia', function (Blueprint $table) {
            $table->text('diagnostico_ia')->nullable()->change();
            $table->text('recomendacion_ia')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('reportes_contingencia', function (Blueprint $table) {
            $table->text('diagnostico_ia')->nullable(false)->change();
            $table->text('recomendacion_ia')->nullable(false)->change();
        });
    }
};