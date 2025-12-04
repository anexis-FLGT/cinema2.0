<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('cinema_sessions', function (Blueprint $table) {
            $table->id('id_session');
            $table->dateTime('date_time_session');
            $table->foreignId('movie_id')->constrained('movies', 'id_movie');
            
            $table->index('id_session');
            $table->index('movie_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('cinema_sessions');
    }
};