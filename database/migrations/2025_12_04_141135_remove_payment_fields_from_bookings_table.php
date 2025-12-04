<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Удаляем поля payment_id, payment_status, amount из таблицы bookings
     */
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            // Проверяем существование колонок перед удалением
            if (Schema::hasColumn('bookings', 'payment_id')) {
                $table->dropColumn('payment_id');
            }
            if (Schema::hasColumn('bookings', 'payment_status')) {
                $table->dropColumn('payment_status');
            }
            if (Schema::hasColumn('bookings', 'amount')) {
                $table->dropColumn('amount');
            }
        });
    }

    /**
     * Reverse the migrations.
     * Возвращаем поля обратно (если нужно откатить)
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            // Добавляем поля обратно
            if (!Schema::hasColumn('bookings', 'payment_id')) {
                $table->string('payment_id')->nullable()->after('seat_id');
            }
            if (!Schema::hasColumn('bookings', 'payment_status')) {
                $table->enum('payment_status', ['ожидание', 'оплачено', 'отменено', 'ожидает_подтверждения'])
                      ->default('ожидание')
                      ->after('payment_id');
            }
            if (!Schema::hasColumn('bookings', 'amount')) {
                $table->decimal('amount', 10, 2)->nullable()->after('payment_status');
            }
        });
    }
};
