<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        $driver = \Illuminate\Support\Facades\DB::getDriverName();
        
        Schema::create('roles', function (Blueprint $table) use ($driver) {
            $table->id('id_role');
            
            // Для SQLite используем string вместо enum
            if ($driver === 'sqlite') {
                $table->string('role_name');
            } else {
                $table->enum('role_name', ['Администратор', 'Пользователь', 'Гость']);
            }
            
            $table->index('id_role');
        });
    }

    public function down()
    {
        Schema::dropIfExists('roles');
    }
};