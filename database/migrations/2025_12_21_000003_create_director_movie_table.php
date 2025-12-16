<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('director_movie', function (Blueprint $table) {
            $table->unsignedBigInteger('movie_id');
            $table->unsignedBigInteger('director_id');

            $table->primary(['movie_id', 'director_id']);

            $table->foreign('movie_id')->references('id_movie')->on('movies')->onDelete('cascade');
            $table->foreign('director_id')->references('id_director')->on('directors')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('director_movie');
    }
};


