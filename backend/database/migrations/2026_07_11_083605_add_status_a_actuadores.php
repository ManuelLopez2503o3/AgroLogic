<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('actuadores', function (Blueprint $table) {
            $table->boolean('bomba_status')->default(false);
            $table->boolean('luces_status')->default(false);
        });
    }

    public function down(): void
    {
        Schema::table('actuadores', function (Blueprint $table) {
            $table->dropColumn(['bomba_status', 'luces_status']);
        });
    }
};