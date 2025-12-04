<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id('id_booking');
            $table->date('show_date');
            $table->time('show_time');
            $table->timestamp('created_ad')->useCurrent();
            $table->foreignId('user_id')->constrained('cinema_users', 'id_user');
            $table->foreignId('movie_id')->constrained('movies', 'id_movie');
            $table->foreignId('session_id')->constrained('cinema_sessions', 'id_session');
            $table->foreignId('hall_id')->constrained('halls', 'id_hall');
            $table->foreignId('seat_id')->constrained('seats', 'id_seat');


            $table->index('id_booking');
            $table->index('user_id');
            $table->index('movie_id');
            $table->index('session_id');
            $table->index('hall_id');
            $table->index('seat_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('bookings');
    }
};