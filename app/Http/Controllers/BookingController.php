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
     * Показать страницу бронирования для конкретного фильма
     */
    public function show($movieId)
    {
        // Автоматически освобождаем истекшие бронирования
        Booking::expireOldBookings();
        
        $movie = Movie::with('genres')->findOrFail($movieId);
        
        // Исправляем пути к изображениям
        $movie->poster = $this->fixPath($movie->poster, 'images/posters/placeholder.jpg');
        $movie->baner = $this->fixPath($movie->baner, 'images/banners/placeholder.jpg');
        
        // Получаем доступные сеансы для этого фильма (только будущие)
        $sessions = Session::where('movie_id', $movieId)
            ->where('date_time_session', '>=', now())
            ->orderBy('date_time_session', 'asc')
            ->get();
        
        // Группируем сеансы по датам
        $sessionsByDate = $sessions->groupBy(function($session) {
            return $session->date_time_session->format('Y-m-d');
        });
        
        return view('booking.show', compact('movie', 'sessions', 'sessionsByDate'));
    }
    
    /**
     * Получить зал и места для выбранного сеанса (AJAX)
     */
    public function getHallSeats(Request $request)
    {
        // Автоматически освобождаем истекшие бронирования
        Booking::expireOldBookings();
        
        $sessionId = $request->input('session_id');
        
        $session = Session::with(['movie', 'hall'])->findOrFail($sessionId);
        
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
        
        $bookedSeatIds = Booking::where('session_id', $sessionId)
            ->whereHas('payment', function($query) {
                $query->where('payment_status', '!=', 'отменено');
            })
            ->where(function($query) use ($expirationTime) {
                // Исключаем истекшие бронирования со статусом "ожидание"
                $query->whereHas('payment', function($q) {
                    $q->where('payment_status', '!=', 'ожидание');
                })
                ->orWhere('created_ad', '>', $expirationTime);
            })
            ->pluck('seat_id')
            ->toArray();
        
        // Помечаем места как забронированные и преобразуем в массив
        $seatsArray = $seats->map(function($seat) use ($bookedSeatIds) {
            return [
                'id_seat' => $seat->id_seat,
                'row_number' => $seat->row_number,
                'seat_number' => $seat->seat_number,
                'status' => $seat->status,
                'hall_id' => $seat->hall_id,
                'is_booked' => in_array($seat->id_seat, $bookedSeatIds) || $seat->status === 'Забронировано',
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
        
        $validated = $request->validate([
            'session_id' => 'required|exists:cinema_sessions,id_session',
            'seat_ids' => 'required|array|min:1|max:7',
            'seat_ids.*' => 'required|exists:seats,id_seat',
        ]);
        
        // Получаем данные сеанса
        $session = Session::with('hall')->findOrFail($validated['session_id']);
        
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
        
        $existingBookings = Booking::where('session_id', $validated['session_id'])
            ->whereIn('seat_id', $seatIds)
            ->whereHas('payment', function($query) {
                $query->where('payment_status', '!=', 'отменено');
            })
            ->where(function($query) use ($expirationTime) {
                // Исключаем истекшие бронирования со статусом "ожидание"
                $query->whereHas('payment', function($q) {
                    $q->where('payment_status', '!=', 'ожидание');
                })
                ->orWhere('created_ad', '>', $expirationTime);
            })
            ->pluck('seat_id')
            ->toArray();
        
        if (!empty($existingBookings)) {
            return back()->with('error', 'Одно или несколько мест уже забронированы на данный сеанс');
        }
        
        // Проверяем, что места не забронированы в статусе
        $bookedSeats = $seats->filter(function($seat) {
            return $seat->status === 'Забронировано';
        });
        
        if ($bookedSeats->isNotEmpty()) {
            return back()->with('error', 'Одно или несколько мест уже забронированы');
        }
        
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
        $booking = Booking::with(['movie', 'session', 'hall', 'seat'])
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
            $allBookings = Booking::with(['movie', 'session', 'hall', 'seat'])
                ->whereIn('id_booking', $bookingIds)
                ->where('user_id', Auth::id())
                ->get();
        }
        
        return view('booking.success', compact('booking', 'allBookings'));
    }
    
    /**
     * Исправление путей к изображениям
     */
    private function fixPath($path, $placeholder)
    {
        if (!$path) return asset($placeholder);
        return asset(ltrim(str_replace('\\', '/', $path), '/'));
    }
}

