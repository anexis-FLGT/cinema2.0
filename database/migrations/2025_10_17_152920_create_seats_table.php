<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('seats', function (Blueprint $table) {
            $table->id('id_seat');
            $table->integer('row_number');
            $table->integer('seat_number');
            $table->enum('status', ['Свободно', 'Забронировано']);
            $table->foreignId('hall_id')->constrained('halls', 'id_hall');
            
            $table->index('id_seat');
            $table->index('hall_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('seats');
    }
};