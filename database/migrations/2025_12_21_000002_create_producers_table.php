<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('producers', function (Blueprint $table) {
            $table->id('id_producer');
            $table->string('name', 255)->unique();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('producers');
    }
};


