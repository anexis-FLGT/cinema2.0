<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('genres', function (Blueprint $table) {
            $table->id('id_genre');
            $table->enum('genre_name', ['Драма', 'Комедия', 'Триллер', 'Боевик', 'Фантастика', 'Детектив', 'Ужасы', 'Мелодрама', 'Фэнтези', 'Вестерн', 'Мультфильм']);
            $table->foreignId('movie_id')->constrained('movies', 'id_movie');
            
            $table->index('id_genre');
            $table->index('movie_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('genres');
    }
};