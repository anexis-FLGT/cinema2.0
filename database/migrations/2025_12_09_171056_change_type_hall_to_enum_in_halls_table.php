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
        // Сначала обновляем существующие записи на одно из допустимых значений
        // Если значение не соответствует новым, устанавливаем 'средний' по умолчанию
        DB::table('halls')
            ->whereNotIn('type_hall', ['большой', 'средний', 'малый'])
            ->update(['type_hall' => 'средний']);
        
        // Теперь изменяем тип колонки на ENUM
        DB::statement("ALTER TABLE `halls` MODIFY `type_hall` ENUM('большой', 'средний', 'малый') NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Возвращаем обратно к строке
        DB::statement("ALTER TABLE `halls` MODIFY `type_hall` VARCHAR(50) NOT NULL");
    }
};
