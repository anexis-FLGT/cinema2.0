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
        $driver = \Illuminate\Support\Facades\DB::getDriverName();
        
        Schema::table('genres', function (Blueprint $table) use ($driver) {
            // Удаляем внешний ключ (только для MySQL)
            if ($driver !== 'sqlite') {
                try {
                    $table->dropForeign(['movie_id']);
                } catch (\Exception $e) {
                    // Игнорируем ошибку, если внешний ключ не существует
                }
            }
            
            // Удаляем индекс (только для MySQL, в SQLite индекс не создается отдельно)
            if ($driver !== 'sqlite') {
                try {
                    $table->dropIndex(['movie_id']);
                } catch (\Exception $e) {
                    // Игнорируем ошибку, если индекс не существует
                }
            }
            
            // Удаляем колонку
            $table->dropColumn('movie_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = \Illuminate\Support\Facades\DB::getDriverName();
        
        Schema::table('genres', function (Blueprint $table) use ($driver) {
            // Восстанавливаем колонку (временно, для отката)
            if ($driver !== 'sqlite') {
                $table->foreignId('movie_id')->nullable()->after('genre_name');
            } else {
                $table->foreignId('movie_id')->nullable();
            }
            $table->foreign('movie_id')->references('id_movie')->on('movies');
            $table->index('movie_id');
        });
    }
};

