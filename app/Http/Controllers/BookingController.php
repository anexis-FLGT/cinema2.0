<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Movie;
use App\Models\Session;
use App\Models\Hall;
use App\Models\Seat;
use App\Models\Booking;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class BookingController extends Controller
{
    /**
     * Показать страницу бронирования для конкретного сеанса
     */
    public function show($sessionId)
    {
        // Проверяем авторизацию
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Для бронирования билетов необходимо войти в систему');
        }
        
        // Проверяем, что пользователь не администратор
        if (Auth::user()->role_id == 1) {
            return redirect()->route('sessions')->with('error', 'Администраторы не могут бронировать билеты');
        }
        
        // Автоматически освобождаем истекшие бронирования
        Booking::expireOldBookings();
        
        // Получаем сеанс с фильмом и залом
        $session = Session::with(['movie.genres', 'hall'])->findOrFail($sessionId);
        
        // Проверяем, что сеанс не архивирован
        if ($session->is_archived) {
            return redirect()->route('sessions')->with('error', 'Этот сеанс недоступен');
        }
        
        // Проверяем, что сеанс еще не прошел
        if ($session->date_time_session < now()) {
            return redirect()->route('sessions')->with('error', 'Этот сеанс уже прошел');
        }
        
        // Проверяем, что у сеанса есть привязанный зал
        if (!$session->hall_id || !$session->hall) {
            return redirect()->route('sessions')->with('error', 'Для данного сеанса не указан зал');
        }
        
        $movie = $session->movie;
        
        // Исправляем пути к изображениям
        $movie->poster = $this->fixPath($movie->poster, 'images/posters/placeholder.jpg');
        $movie->baner = $this->fixPath($movie->baner, 'images/banners/placeholder.jpg');
        
        // Получаем зал с местами
        $hall = $session->hall;
        $seats = Seat::where('hall_id', $hall->id_hall)->orderBy('row_number')->orderBy('seat_number')->get();
        
        // Получаем забронированные места для этого сеанса
        $paymentTimeoutMinutes = 10;
        $expirationTime = Carbon::now()->subMinutes($paymentTimeoutMinutes);
        
        $bookedSeatIds = Booking::where('session_id', $sessionId)
            ->where(function($query) use ($expirationTime) {
                $query->whereHas('payment', function($q) {
                    $q->whereIn('payment_status', ['оплачено', 'ожидает_подтверждения']);
                })
                ->orWhere(function($subQuery) use ($expirationTime) {
                    $subQuery->where('created_ad', '>', $expirationTime)
                             ->whereHas('payment', function($q) {
                                 $q->where('payment_status', 'ожидание');
                             });
                });
            })
            ->pluck('seat_id')
            ->toArray();
        
        // Помечаем места как забронированные
        foreach ($seats as $seat) {
            $seat->is_booked = in_array($seat->id_seat, $bookedSeatIds);
        }
        
        $hall->seats = $seats;
        
        return view('booking.show', compact('session', 'movie', 'hall'));
    }
    
    /**
     * Получить зал и места для выбранного сеанса (AJAX)
     */
    public function getHallSeats(Request $request)
    {
        // Проверяем, что пользователь не администратор
        if (Auth::check() && Auth::user()->role_id == 1) {
            return response()->json(['error' => 'Администраторы не могут бронировать билеты'], 403);
        }
        
        // Автоматически освобождаем истекшие бронирования
        Booking::expireOldBookings();
        
        $sessionId = $request->input('session_id');
        
        $session = Session::with(['movie', 'hall'])->findOrFail($sessionId);
        
        // Проверяем, что сеанс не архивирован
        if ($session->is_archived) {
            return response()->json(['error' => 'Этот сеанс недоступен'], 404);
        }
        
        // Проверяем, что у сеанса есть привязанный зал
        if (!$session->hall_id || !$session->hall) {
            return response()->json([
                'error' => 'Для данного сеанса не указан зал'
            ], 404);
        }
        
        $hall = $session->hall;
        
        // Получаем места для этого зала
        $seats = Seat::where('hall_id', $hall->id_hall)->get();
        
        // Получаем забронированные места для этого сеанса (исключаем истекшие)
        $paymentTimeoutMinutes = 10; // Время на оплату в минутах
        $expirationTime = Carbon::now()->subMinutes($paymentTimeoutMinutes);
        
        // ВАЖНО: Проверяем ТОЛЬКО для конкретного session_id
        // Ищем активные бронирования (оплаченные, ожидающие подтверждения, или не истекшие "ожидание")
        $bookedSeatIds = Booking::where('session_id', $sessionId)
            ->where(function($query) use ($expirationTime) {
                // Оплаченные или ожидающие подтверждения
                $query->whereHas('payment', function($q) {
                    $q->whereIn('payment_status', ['оплачено', 'ожидает_подтверждения']);
                })
                // Или "ожидание", но не истекшие
                ->orWhere(function($subQuery) use ($expirationTime) {
                    $subQuery->where('created_ad', '>', $expirationTime)
                             ->whereHas('payment', function($q) {
                                 $q->where('payment_status', 'ожидание');
                             });
                });
            })
            ->pluck('seat_id')
            ->toArray();
        
        // Помечаем места как забронированные ТОЛЬКО на основе bookings для ЭТОГО КОНКРЕТНОГО СЕАНСА
        // НЕ используем статус места, так как одно место может быть забронировано на разные сеансы
        $seatsArray = $seats->map(function($seat) use ($bookedSeatIds) {
            return [
                'id_seat' => $seat->id_seat,
                'row_number' => $seat->row_number,
                'seat_number' => $seat->seat_number,
                'status' => $seat->status,
                'hall_id' => $seat->hall_id,
                'is_booked' => in_array($seat->id_seat, $bookedSeatIds), // ТОЛЬКО по bookings для этого session_id
            ];
        })->values();
        
        return response()->json([
            'hall' => [
                'id_hall' => $hall->id_hall,
                'hall_name' => $hall->hall_name,
                'type_hall' => $hall->type_hall,
                'quantity_seats' => $hall->quantity_seats,
                'seats' => $seatsArray,
            ],
            'session' => [
                'id_session' => $session->id_session,
                'date_time_session' => $session->date_time_session->format('Y-m-d H:i:s'),
                'movie' => [
                    'id_movie' => $session->movie->id_movie,
                    'movie_title' => $session->movie->movie_title,
                ],
            ],
        ]);
    }
    
    /**
     * Сохранить бронирование (поддержка множественного бронирования до 7 мест)
     */
    public function store(Request $request)
    {
        // Автоматически освобождаем истекшие бронирования
        Booking::expireOldBookings();
        
        // Проверяем авторизацию
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Необходимо войти в систему для бронирования');
        }
        
        // Проверяем, что пользователь не администратор
        if (Auth::user()->role_id == 1) {
            return redirect()->route('sessions')->with('error', 'Администраторы не могут бронировать билеты');
        }
        
        $validated = $request->validate([
            'session_id' => 'required|exists:cinema_sessions,id_session',
            'seat_ids' => 'required|array|min:1|max:7',
            'seat_ids.*' => 'required|exists:seats,id_seat',
        ]);
        
        // Получаем данные сеанса
        $session = Session::with('hall')->findOrFail($validated['session_id']);
        
        // Проверяем, что сеанс не архивирован
        if ($session->is_archived) {
            return redirect()->route('sessions')->with('error', 'Этот сеанс недоступен');
        }
        
        // Проверяем, что у сеанса есть привязанный зал
        if (!$session->hall_id || !$session->hall) {
            return back()->with('error', 'Для данного сеанса не указан зал');
        }
        
        $hallId = $session->hall_id;
        $seatIds = $validated['seat_ids'];
        
        // Проверяем, что все места принадлежат залу сеанса
        $seats = Seat::whereIn('id_seat', $seatIds)->get();
        foreach ($seats as $seat) {
            if ($seat->hall_id != $hallId) {
                return back()->with('error', 'Одно из выбранных мест не принадлежит залу сеанса');
            }
        }
        
        // Проверяем, не забронированы ли места уже для этого сеанса (исключаем отмененные и истекшие)
        $paymentTimeoutMinutes = 10; // Время на оплату в минутах
        $expirationTime = Carbon::now()->subMinutes($paymentTimeoutMinutes);
        
        // ВАЖНО: Проверяем ТОЛЬКО для конкретного session_id и конкретных seat_id
        // Ищем активные бронирования (оплаченные, ожидающие подтверждения, или не истекшие "ожидание")
        $existingBookings = Booking::where('session_id', $validated['session_id'])
            ->whereIn('seat_id', $seatIds)
            ->where(function($query) use ($expirationTime) {
                // Оплаченные или ожидающие подтверждения
                $query->whereHas('payment', function($q) {
                    $q->whereIn('payment_status', ['оплачено', 'ожидает_подтверждения']);
                })
                // Или "ожидание", но не истекшие
                ->orWhere(function($subQuery) use ($expirationTime) {
                    $subQuery->where('created_ad', '>', $expirationTime)
                             ->whereHas('payment', function($q) {
                                 $q->where('payment_status', 'ожидание');
                             });
                });
            })
            ->exists();
        
        if ($existingBookings) {
            return back()->with('error', 'Одно или несколько мест уже забронированы на данный сеанс');
        }
        
        // НЕ проверяем статус места, так как одно место может быть забронировано на разные сеансы
        // Забронированность определяется ТОЛЬКО по bookings для конкретного session_id
        
        // Сохраняем данные в сессии для создания платежа
        session([
            'pending_booking' => [
                'session_id' => $validated['session_id'],
                'seat_ids' => $seatIds,
            ]
        ]);
        
        // Перенаправляем на страницу подтверждения, которая отправит POST-запрос
        return redirect()->route('payment.confirm');
    }
    
    /**
     * Страница успешного бронирования
     */
    public function success($bookingId)
    {
        $booking = Booking::with(['session.movie', 'session', 'hall', 'seat'])
            ->findOrFail($bookingId);
        
        // Проверяем, что бронирование принадлежит текущему пользователю
        if (Auth::check() && $booking->user_id != Auth::id()) {
            abort(403);
        }
        
        // Получаем все бронирования из последней сессии (если есть)
        $allBookings = collect([$booking]);
        if (session()->has('last_booking_ids')) {
            $bookingIds = session('last_booking_ids');
            session()->forget('last_booking_ids'); // Удаляем после использования
            
            // Загружаем все бронирования
            $allBookings = Booking::with(['session.movie', 'session', 'hall', 'seat'])
                ->whereIn('id_booking', $bookingIds)
                ->where('user_id', Auth::id())
                ->get();
        }
        
        return view('booking.success', compact('booking', 'allBookings'));
    }
    

// Исправление путей к изображениям
    private function fixPath($path, $placeholder)
    {
        if (!$path) return asset($placeholder);
        return asset(ltrim(str_replace('\\', '/', $path), '/'));
    }
}

