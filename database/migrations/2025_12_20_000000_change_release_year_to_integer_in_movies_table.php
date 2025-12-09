<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Изменяем тип колонки release_year с YEAR на INTEGER
        DB::statement('ALTER TABLE movies MODIFY COLUMN release_year INTEGER NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Возвращаем тип YEAR (но это может вызвать проблемы, если есть значения < 1901)
        DB::statement('ALTER TABLE movies MODIFY COLUMN release_year YEAR NULL');
    }
};

