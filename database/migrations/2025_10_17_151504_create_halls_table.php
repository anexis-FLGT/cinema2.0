<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('halls', function (Blueprint $table) {
            $table->id('id_hall');
            $table->string('hall_name', 30);
            $table->string('quantity_seats', 45);
            $table->string('type_hall', 50);
            $table->text('description_hall');
            $table->string('hall_photo', 255)->nullable();
            
            $table->index('id_hall');
        });
    }

    public function down()
    {
        Schema::dropIfExists('halls');
    }
};