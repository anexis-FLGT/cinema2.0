<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('genres', function (Blueprint $table) {
            // Удаляем внешний ключ
            $table->dropForeign(['movie_id']);
            // Удаляем индекс
            $table->dropIndex(['movie_id']);
            // Удаляем колонку
            $table->dropColumn('movie_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('genres', function (Blueprint $table) {
            // Восстанавливаем колонку (временно, для отката)
            $table->foreignId('movie_id')->nullable()->after('genre_name');
            $table->foreign('movie_id')->references('id_movie')->on('movies');
            $table->index('movie_id');
        });
    }
};

