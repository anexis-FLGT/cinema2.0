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
        // Проверяем, существует ли уже поле hall_id
        $hasColumn = Schema::hasColumn('cinema_sessions', 'hall_id');
        
        if (!$hasColumn) {
            // Сначала добавляем поле как nullable
            Schema::table('cinema_sessions', function (Blueprint $table) {
                $table->unsignedBigInteger('hall_id')->nullable()->after('movie_id');
            });
        }

        // Заполняем существующие записи первым доступным залом (если есть NULL значения)
        $firstHall = DB::table('halls')->first();
        if ($firstHall) {
            // Заполняем NULL значения
            DB::table('cinema_sessions')
                ->whereNull('hall_id')
                ->update(['hall_id' => $firstHall->id_hall]);
            
            // Исправляем несуществующие hall_id (если есть)
            $validHallIds = DB::table('halls')->pluck('id_hall')->toArray();
            DB::table('cinema_sessions')
                ->whereNotIn('hall_id', $validHallIds)
                ->update(['hall_id' => $firstHall->id_hall]);
        } else {
            // Если залов нет, создаем хотя бы один зал
            $defaultHallId = DB::table('halls')->insertGetId([
                'hall_name' => 'Зал 1',
                'quantity_seats' => '100',
                'type_hall' => 'Обычный',
                'description_hall' => 'Основной зал кинотеатра',
            ]);
            
            // Заполняем все записи этим залом
            DB::table('cinema_sessions')
                ->whereNull('hall_id')
                ->orWhereNotIn('hall_id', [$defaultHallId])
                ->update(['hall_id' => $defaultHallId]);
        }

        // Используем raw SQL для изменения столбца на NOT NULL (если еще не NOT NULL)
        // Это более надежно, чем метод change()
        $columnInfo = DB::select("SHOW COLUMNS FROM `cinema_sessions` LIKE 'hall_id'");
        if (!empty($columnInfo) && $columnInfo[0]->Null === 'YES') {
            DB::statement('ALTER TABLE `cinema_sessions` MODIFY `hall_id` BIGINT UNSIGNED NOT NULL');
        }

        // Проверяем, существует ли уже внешний ключ
        $foreignKeys = DB::select("
            SELECT CONSTRAINT_NAME 
            FROM information_schema.KEY_COLUMN_USAGE 
            WHERE TABLE_SCHEMA = DATABASE() 
            AND TABLE_NAME = 'cinema_sessions' 
            AND COLUMN_NAME = 'hall_id' 
            AND REFERENCED_TABLE_NAME IS NOT NULL
        ");

        // Добавляем внешний ключ и индекс, если их еще нет
        if (empty($foreignKeys)) {
            Schema::table('cinema_sessions', function (Blueprint $table) {
                $table->foreign('hall_id')->references('id_hall')->on('halls');
            });
        }

        // Проверяем, существует ли индекс
        $indexes = DB::select("
            SHOW INDEX FROM `cinema_sessions` WHERE Column_name = 'hall_id' AND Key_name != 'PRIMARY'
        ");
        
        if (empty($indexes)) {
            Schema::table('cinema_sessions', function (Blueprint $table) {
                $table->index('hall_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cinema_sessions', function (Blueprint $table) {
            $table->dropForeign(['hall_id']);
            $table->dropIndex(['hall_id']);
            $table->dropColumn('hall_id');
        });
    }
};

