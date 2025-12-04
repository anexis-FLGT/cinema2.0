<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('cinema_users', function (Blueprint $table) {
            $table->id('id_user');
            $table->text('last_name');
            $table->text('first_name');
            $table->text('middle_name')->nullable();
            $table->string('phone', 18);
            $table->string('login', 45)->unique();
            $table->string('password', 255);
            $table->foreignId('role_id')->constrained('roles', 'id_role');

            
            $table->index('id_user');
            $table->index('role_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('cinema_users');
    }
};