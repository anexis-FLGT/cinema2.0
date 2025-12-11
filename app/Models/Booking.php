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
        'created_ad',
        'user_id',
        'session_id',
        'seat_id',
    ];

    protected $casts = [
        'created_ad' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id_user');
    }

    public function movie()
    {
        return $this->hasOneThrough(
            Movie::class,      // Related model (movies)
            Session::class,    // Through model (cinema_sessions)
            'movie_id',        // Foreign key on sessions table (sessions.movie_id -> movies.id_movie)
            'id_movie',        // Foreign key on movies table
            'session_id',      // Local key on bookings table
            'id_session'       // Local key on sessions table
        );
    }

    public function session()
    {
        return $this->belongsTo(Session::class, 'session_id', 'id_session');
    }

    public function hall()
    {
        return $this->hasOneThrough(
            Hall::class,        // Related model (halls)
            Session::class,     // Through model (cinema_sessions)
            'hall_id',          // Foreign key on sessions table (sessions.hall_id -> halls.id_hall)
            'id_hall',          // Foreign key on halls table
            'session_id',       // Local key on bookings table
            'id_session'        // Local key on sessions table
        );
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
     * Accessor для получения movie через session (для обратной совместимости)
     */
    public function getMovieAttribute()
    {
        return $this->session->movie ?? null;
    }

    /**
     * Accessor для получения hall через session (для обратной совместимости)
     */
    public function getHallAttribute()
    {
        return $this->session->hall ?? null;
    }

    /**
     * Освобождает места с истекшим временем оплаты бронирования
     * Вызывается автоматически при работе с бронированиями
     * 
     * @param int $paymentTimeoutMinutes Время на оплату в минутах (по умолчанию 10)
     * @return int Количество освобожденных бронирований
     */
    public static function expireOldBookings($paymentTimeoutMinutes = 10)
    {
        $expirationTime = Carbon::now()->subMinutes($paymentTimeoutMinutes);

        // Находим все бронирования со статусом "ожидание", созданные до времени истечения
        // Включаем как бронирования с платежами со статусом "ожидание", так и без платежей
        $expiredBookings = static::where(function($query) {
                $query->whereHas('payment', function($q) {
                    $q->where('payment_status', 'ожидание');
                })->orWhereDoesntHave('payment');
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
                // Обновляем статус платежа на "отменено", если платеж существует
                if ($booking->payment) {
                    $booking->payment->payment_status = 'отменено';
                    $booking->payment->save();
                }

                // НЕ меняем статус места - забронированность определяется только по bookings для конкретного session_id
                // Место может быть забронировано на другие сеансы, поэтому не нужно менять его статус
                $freedSeats++;

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


