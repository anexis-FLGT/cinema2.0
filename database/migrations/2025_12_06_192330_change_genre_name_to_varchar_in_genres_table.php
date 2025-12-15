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
        // Проверяем тип базы данных
        $driver = DB::getDriverName();
        
        if ($driver === 'mysql') {
            // Для MySQL изменяем тип колонки genre_name с enum на varchar(45)
            DB::statement('ALTER TABLE `genres` MODIFY COLUMN `genre_name` VARCHAR(45) NOT NULL');
        }
        // Для SQLite изменение типа колонки не поддерживается напрямую
        // В SQLite VARCHAR обрабатывается как TEXT, что совместимо
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Проверяем тип базы данных
        $driver = DB::getDriverName();
        
        if ($driver === 'mysql') {
            // Возвращаем обратно к enum (если нужно откатить)
            DB::statement("ALTER TABLE `genres` MODIFY COLUMN `genre_name` ENUM('Драма', 'Комедия', 'Триллер', 'Боевик', 'Фантастика', 'Детектив', 'Ужасы', 'Мелодрама', 'Фэнтези', 'Вестерн', 'Мультфильм') NOT NULL");
        }
        // Для SQLite изменение типа колонки не поддерживается напрямую
    }
};
