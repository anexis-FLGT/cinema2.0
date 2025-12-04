<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Переносим данные о платежах из таблицы bookings в таблицу payments
     */
    public function up(): void
    {
        // Проверяем, что таблица payments существует
        if (!Schema::hasTable('payments')) {
            return;
        }

        // Проверяем, что в bookings есть поля для переноса
        if (!Schema::hasColumn('bookings', 'payment_id') || 
            !Schema::hasColumn('bookings', 'payment_status') || 
            !Schema::hasColumn('bookings', 'amount')) {
            return;
        }

        // Получаем все бронирования с данными о платежах
        $bookings = DB::table('bookings')
            ->whereNotNull('payment_id')
            ->orWhereNotNull('payment_status')
            ->orWhereNotNull('amount')
            ->get();

        // Переносим данные в таблицу payments
        foreach ($bookings as $booking) {
            // Проверяем, не существует ли уже payment для этого booking
            $existingPayment = DB::table('payments')
                ->where('booking_id', $booking->id_booking)
                ->first();

            if (!$existingPayment) {
                DB::table('payments')->insert([
                    'payment_id' => $booking->payment_id,
                    'payment_status' => $booking->payment_status ?? 'ожидание',
                    'amount' => $booking->amount,
                    'booking_id' => $booking->id_booking,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // Также обрабатываем бронирования без payment_id, но с payment_status или amount
        $bookingsWithoutPaymentId = DB::table('bookings')
            ->whereNull('payment_id')
            ->where(function($query) {
                $query->whereNotNull('payment_status')
                      ->orWhereNotNull('amount');
            })
            ->get();

        foreach ($bookingsWithoutPaymentId as $booking) {
            $existingPayment = DB::table('payments')
                ->where('booking_id', $booking->id_booking)
                ->first();

            if (!$existingPayment) {
                DB::table('payments')->insert([
                    'payment_id' => null,
                    'payment_status' => $booking->payment_status ?? 'ожидание',
                    'amount' => $booking->amount,
                    'booking_id' => $booking->id_booking,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     * Возвращаем данные обратно в bookings (если нужно откатить)
     */
    public function down(): void
    {
        // Получаем все payments
        $payments = DB::table('payments')->get();

        // Возвращаем данные в bookings
        foreach ($payments as $payment) {
            DB::table('bookings')
                ->where('id_booking', $payment->booking_id)
                ->update([
                    'payment_id' => $payment->payment_id,
                    'payment_status' => $payment->payment_status,
                    'amount' => $payment->amount,
                ]);
        }
    }
};
