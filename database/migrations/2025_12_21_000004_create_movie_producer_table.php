<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('movie_producer', function (Blueprint $table) {
            $table->unsignedBigInteger('movie_id');
            $table->unsignedBigInteger('producer_id');

            $table->primary(['movie_id', 'producer_id']);

            $table->foreign('movie_id')->references('id_movie')->on('movies')->onDelete('cascade');
            $table->foreign('producer_id')->references('id_producer')->on('producers')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('movie_producer');
    }
};


