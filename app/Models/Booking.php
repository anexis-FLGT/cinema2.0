<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Booking extends Model
{
    protected $table = 'bookings';
    protected $primaryKey = 'id_booking';
    public $timestamps = false;

    protected $fillable = [
        'show_date',
        'show_time',
        'created_ad',
        'user_id',
        'movie_id',
        'session_id',
        'hall_id',
        'seat_id',
    ];

    protected $casts = [
        'show_date' => 'date',
        'show_time' => 'datetime',
        'created_ad' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id_user');
    }

    public function movie()
    {
        return $this->belongsTo(Movie::class, 'movie_id', 'id_movie');
    }

    public function session()
    {
        return $this->belongsTo(Session::class, 'session_id', 'id_session');
    }

    public function hall()
    {
        return $this->belongsTo(Hall::class, 'hall_id', 'id_hall');
    }

    public function seat()
    {
        return $this->belongsTo(Seat::class, 'seat_id', 'id_seat');
    }

    /**
     * Связь с платежом
     */
    public function payment()
    {
        return $this->hasOne(Payment::class, 'booking_id', 'id_booking');
    }

    /**
     * Освобождает места с истекшим временем оплаты бронирования
     * Вызывается автоматически при работе с бронированиями
     * 
     * @param int $paymentTimeoutMinutes Время на оплату в минутах (по умолчанию 15)
     * @return int Количество освобожденных бронирований
     */
    public static function expireOldBookings($paymentTimeoutMinutes = 15)
    {
        $expirationTime = Carbon::now()->subMinutes($paymentTimeoutMinutes);

        // Находим все бронирования со статусом "ожидание", созданные до времени истечения
        $expiredBookings = static::whereHas('payment', function($query) {
                $query->where('payment_status', 'ожидание');
            })
            ->where('created_ad', '<=', $expirationTime)
            ->with(['payment', 'seat'])
            ->get();

        if ($expiredBookings->isEmpty()) {
            return 0;
        }

        $freedSeats = 0;
        $deletedBookings = 0;

        foreach ($expiredBookings as $booking) {
            try {
                // Обновляем статус платежа на "отменено"
                if ($booking->payment) {
                    $booking->payment->payment_status = 'отменено';
                    $booking->payment->save();
                }

                // Освобождаем место, если оно было помечено как "Забронировано"
                if ($booking->seat) {
                    // Проверяем, что нет других активных бронирований на это место для этого сеанса
                    $activeBookings = static::where('seat_id', $booking->seat_id)
                        ->where('session_id', $booking->session_id)
                        ->where('id_booking', '!=', $booking->id_booking)
                        ->whereHas('payment', function($query) {
                            $query->whereIn('payment_status', ['ожидание', 'оплачено', 'ожидает_подтверждения']);
                        })
                        ->exists();

                    // Если нет других активных бронирований, освобождаем место
                    if (!$activeBookings) {
                        if ($booking->seat->status === 'Забронировано') {
                            $booking->seat->status = 'Свободно';
                            $booking->seat->save();
                        }
                        $freedSeats++;
                    }
                } else {
                    $freedSeats++;
                }

                // Удаляем бронирование
                $booking->delete();
                $deletedBookings++;

            } catch (\Exception $e) {
                // Логируем ошибку, но продолжаем обработку
                \Log::error("Ошибка при освобождении бронирования ID {$booking->id_booking}: " . $e->getMessage());
            }
        }

        return $deletedBookings;
    }
}


