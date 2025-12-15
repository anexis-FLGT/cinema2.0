<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        $driver = \Illuminate\Support\Facades\DB::getDriverName();
        
        Schema::create('genres', function (Blueprint $table) use ($driver) {
            $table->id('id_genre');
            
            // Для SQLite используем string вместо enum
            if ($driver === 'sqlite') {
                $table->string('genre_name');
            } else {
                $table->enum('genre_name', ['Драма', 'Комедия', 'Триллер', 'Боевик', 'Фантастика', 'Детектив', 'Ужасы', 'Мелодрама', 'Фэнтези', 'Вестерн', 'Мультфильм']);
            }
            
            // Внешний ключ на movies создавать не нужно, так как он удаляется позже
            // Вместо этого создаем nullable колонку, которая будет удалена в следующей миграции
            if ($driver !== 'sqlite') {
                $table->foreignId('movie_id')->nullable()->constrained('movies', 'id_movie');
            } else {
                $table->unsignedBigInteger('movie_id')->nullable();
            }
            
            $table->index('id_genre');
            if ($driver !== 'sqlite') {
                $table->index('movie_id');
            }
        });
    }

    public function down()
    {
        Schema::dropIfExists('genres');
    }
};