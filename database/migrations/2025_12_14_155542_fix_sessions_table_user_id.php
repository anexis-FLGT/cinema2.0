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
        $driver = DB::getDriverName();
        
        Schema::table('sessions', function (Blueprint $table) use ($driver) {
            // Удаляем внешний ключ, если он существует (только для MySQL)
            if ($driver === 'mysql') {
                try {
                    // Проверяем, существует ли внешний ключ
                    $foreignKeys = DB::select("
                        SELECT CONSTRAINT_NAME 
                        FROM information_schema.KEY_COLUMN_USAGE 
                        WHERE TABLE_SCHEMA = DATABASE() 
                        AND TABLE_NAME = 'sessions' 
                        AND COLUMN_NAME = 'user_id' 
                        AND REFERENCED_TABLE_NAME IS NOT NULL
                    ");
                    
                    if (!empty($foreignKeys)) {
                        foreach ($foreignKeys as $fk) {
                            $table->dropForeign([$fk->CONSTRAINT_NAME]);
                        }
                    }
                } catch (\Exception $e) {
                    // Игнорируем ошибку, если внешний ключ не существует
                }
            }
        });
        
        // Изменяем тип поля user_id на unsignedBigInteger (без внешнего ключа)
        Schema::table('sessions', function (Blueprint $table) use ($driver) {
            if ($driver === 'mysql') {
                // Для MySQL изменяем тип колонки
                DB::statement('ALTER TABLE `sessions` MODIFY `user_id` BIGINT UNSIGNED NULL');
            }
            // Для SQLite ничего не делаем, так как там тип не важен
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Восстанавливаем внешний ключ (если нужно откатить)
        // Но мы не знаем, на какую таблицу он был, поэтому просто оставляем как есть
    }
};
