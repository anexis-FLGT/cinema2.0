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
        // Сначала изменяем enum на новые значения (временно добавляем русские значения)
        DB::statement("ALTER TABLE `bookings` MODIFY COLUMN `payment_status` ENUM('pending', 'succeeded', 'canceled', 'waiting_for_capture', 'ожидание', 'оплачено', 'отменено', 'ожидает_подтверждения') DEFAULT 'ожидание'");

        // Обновляем существующие значения статусов на русские
        DB::table('bookings')
            ->where('payment_status', 'pending')
            ->update(['payment_status' => 'ожидание']);

        DB::table('bookings')
            ->where('payment_status', 'succeeded')
            ->update(['payment_status' => 'оплачено']);

        DB::table('bookings')
            ->where('payment_status', 'canceled')
            ->update(['payment_status' => 'отменено']);

        DB::table('bookings')
            ->where('payment_status', 'waiting_for_capture')
            ->update(['payment_status' => 'ожидает_подтверждения']);

        // Теперь удаляем английские значения из enum
        DB::statement("ALTER TABLE `bookings` MODIFY COLUMN `payment_status` ENUM('ожидание', 'оплачено', 'отменено', 'ожидает_подтверждения') DEFAULT 'ожидание'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Возвращаем английские значения
        DB::table('bookings')
            ->where('payment_status', 'ожидание')
            ->update(['payment_status' => 'pending']);

        DB::table('bookings')
            ->where('payment_status', 'оплачено')
            ->update(['payment_status' => 'succeeded']);

        DB::table('bookings')
            ->where('payment_status', 'отменено')
            ->update(['payment_status' => 'canceled']);

        DB::table('bookings')
            ->where('payment_status', 'ожидает_подтверждения')
            ->update(['payment_status' => 'waiting_for_capture']);

        DB::statement("ALTER TABLE `bookings` MODIFY COLUMN `payment_status` ENUM('pending', 'succeeded', 'canceled', 'waiting_for_capture') DEFAULT 'pending'");
    }
};

