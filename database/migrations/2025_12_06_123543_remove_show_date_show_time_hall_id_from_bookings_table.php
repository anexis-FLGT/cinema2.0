<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            // Удаляем внешний ключ hall_id
            if (Schema::hasColumn('bookings', 'hall_id')) {
                $table->dropForeign(['hall_id']);
                $table->dropIndex(['hall_id']);
                $table->dropColumn('hall_id');
            }
            
            // Удаляем show_date и show_time
            if (Schema::hasColumn('bookings', 'show_date')) {
                $table->dropColumn('show_date');
            }
            if (Schema::hasColumn('bookings', 'show_time')) {
                $table->dropColumn('show_time');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            // Восстанавливаем колонки для отката
            $table->date('show_date')->after('created_ad');
            $table->time('show_time')->after('show_date');
            $table->foreignId('hall_id')->after('session_id')->constrained('halls', 'id_hall');
            $table->index('hall_id');
        });
    }
};
