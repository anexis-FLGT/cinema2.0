<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('movies', function (Blueprint $table) {
            $table->id('id_movie');
            $table->string('movie_title', 75);
            $table->string('duration', 12);
            $table->enum('age_limit', ['0+', '6+', '12+', '16+', '18+']);
            $table->text('description');
            $table->text('producer');
            $table->string('poster', 255)->nullable();
            $table->string('baner', 255)->nullable();
            
            $table->index('id_movie');
        });
    }

    public function down()
    {
        Schema::dropIfExists('movies');
    }
};