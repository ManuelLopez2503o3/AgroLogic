<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('plantas', 'user_id')) {
            Schema::table('plantas', function (Blueprint $table) {
                $table->unsignedBigInteger('user_id')->after('id');
            });
        } else {
            DB::statement('ALTER TABLE plantas MODIFY user_id BIGINT UNSIGNED NOT NULL');
        }

        $foreignKeyExists = DB::table('information_schema.TABLE_CONSTRAINTS')
            ->where('CONSTRAINT_SCHEMA', DB::getDatabaseName())
            ->where('TABLE_NAME', 'plantas')
            ->where('CONSTRAINT_NAME', 'plantas_user_id_foreign')
            ->exists();

        if (!$foreignKeyExists) {
            Schema::table('plantas', function (Blueprint $table) {
                $table->foreign('user_id')
                      ->references('id')
                      ->on('users')
                      ->onDelete('cascade');
            });
        }
    }

    public function down(): void
    {
        Schema::table('plantas', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
        });
    }
};