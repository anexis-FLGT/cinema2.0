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
        
        // Сначала обновляем существующие записи на одно из допустимых значений
        // Если значение не соответствует новым, устанавливаем 'средний' по умолчанию
        DB::table('halls')
            ->whereNotIn('type_hall', ['большой', 'средний', 'малый'])
            ->update(['type_hall' => 'средний']);
        
        if ($driver === 'mysql') {
            // Для MySQL изменяем тип колонки на ENUM
            DB::statement("ALTER TABLE `halls` MODIFY `type_hall` ENUM('большой', 'средний', 'малый') NOT NULL");
        }
        // Для SQLite изменение типа колонки не поддерживается напрямую
        // В SQLite ENUM обрабатывается как TEXT, что совместимо
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Проверяем тип базы данных
        $driver = DB::getDriverName();
        
        if ($driver === 'mysql') {
            // Возвращаем обратно к строке
            DB::statement("ALTER TABLE `halls` MODIFY `type_hall` VARCHAR(50) NOT NULL");
        }
        // Для SQLite изменение типа колонки не поддерживается напрямую
    }
};
