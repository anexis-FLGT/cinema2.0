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
        // Изменяем тип колонки genre_name с enum на varchar(45)
        DB::statement('ALTER TABLE `genres` MODIFY COLUMN `genre_name` VARCHAR(45) NOT NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Возвращаем обратно к enum (если нужно откатить)
        DB::statement("ALTER TABLE `genres` MODIFY COLUMN `genre_name` ENUM('Драма', 'Комедия', 'Триллер', 'Боевик', 'Фантастика', 'Детектив', 'Ужасы', 'Мелодрама', 'Фэнтези', 'Вестерн', 'Мультфильм') NOT NULL");
    }
};
