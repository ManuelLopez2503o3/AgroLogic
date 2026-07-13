<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reportes_contingencia', function (Blueprint $table) {
            $table->integer('duracion_segundos')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('reportes_contingencia', function (Blueprint $table) {
            $table->integer('duracion_segundos')->nullable(false)->change();
        });
    }
}; 