<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Session;
use App\Models\Movie;
use App\Models\Hall;
use App\Models\Booking;
use App\Models\Payment;

class SessionController extends Controller
{
    /**
     * Отображение списка сеансов
     */
    public function index()
    {
        $sessions = Session::with(['movie', 'hall'])
            ->where('is_archived', false)
            ->orderBy('date_time_session', 'asc')
            ->paginate(10);
        
        $movies = Movie::all();
        $halls = Hall::all();

        return view('admin.sessions', compact('sessions', 'movies', 'halls'));
    }

    /**
     * Сохранение нового сеанса
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'movie_id' => 'required|exists:movies,id_movie',
            'hall_id' => 'required|exists:halls,id_hall',
            'date_time_session' => 'required|date|after_or_equal:now',
        ]);

        // Проверка на полностью одинаковый сеанс (movie_id, hall_id, date_time_session)
        $sessionDateTime = \Carbon\Carbon::parse($validated['date_time_session']);
        $timeString = $sessionDateTime->format('Y-m-d H:i');
        
        $existingSession = Session::where('movie_id', $validated['movie_id'])
            ->where('hall_id', $validated['hall_id'])
            ->where('is_archived', false)
            ->whereRaw("DATE_FORMAT(date_time_session, '%Y-%m-%d %H:%i') = ?", [$timeString])
            ->first();
        
        if ($existingSession) {
            $existingMovie = $existingSession->movie->movie_title ?? 'неизвестный фильм';
            $existingHall = $existingSession->hall->hall_name ?? 'неизвестный зал';
            $existingTime = \Carbon\Carbon::parse($existingSession->date_time_session)->locale('ru')->isoFormat('D MMMM YYYY, HH:mm');
            return redirect()->route('admin.sessions.index')
                ->with('error', "Такой сеанс уже существует! Фильм: {$existingMovie}, Зал: {$existingHall}, Время: {$existingTime}.")
                ->withInput();
        }

        Session::create([
            'movie_id' => $validated['movie_id'],
            'hall_id' => $validated['hall_id'],
            'date_time_session' => $validated['date_time_session'],
        ]);

        return redirect()->route('admin.sessions.index')->with('success', 'Сеанс добавлен.');
    }

    /**
     * Обновление существующего сеанса
     */
    public function update(Request $request, $id)
    {
        $session = Session::findOrFail($id);

        $validated = $request->validate([
            'movie_id' => 'required|exists:movies,id_movie',
            'hall_id' => 'required|exists:halls,id_hall',
            'date_time_session' => 'required|date',
        ]);

        // Проверка на полностью одинаковый сеанс (исключая текущий сеанс)
        $sessionDateTime = \Carbon\Carbon::parse($validated['date_time_session']);
        $timeString = $sessionDateTime->format('Y-m-d H:i');
        
        $existingSession = Session::where('movie_id', $validated['movie_id'])
            ->where('hall_id', $validated['hall_id'])
            ->where('is_archived', false)
            ->whereRaw("DATE_FORMAT(date_time_session, '%Y-%m-%d %H:%i') = ?", [$timeString])
            ->where('id_session', '!=', $id)
            ->first();
        
        if ($existingSession) {
            $existingMovie = $existingSession->movie->movie_title ?? 'неизвестный фильм';
            $existingHall = $existingSession->hall->hall_name ?? 'неизвестный зал';
            $existingTime = \Carbon\Carbon::parse($existingSession->date_time_session)->locale('ru')->isoFormat('D MMMM YYYY, HH:mm');
            return redirect()->route('admin.sessions.index')
                ->with('error', "Такой сеанс уже существует! Фильм: {$existingMovie}, Зал: {$existingHall}, Время: {$existingTime}.")
                ->withInput();
        }

        $session->update([
            'movie_id' => $validated['movie_id'],
            'hall_id' => $validated['hall_id'],
            'date_time_session' => $validated['date_time_session'],
        ]);

        return redirect()->route('admin.sessions.index')->with('success', 'Сеанс обновлён.');
    }

    /**
     * Архивирование сеанса (вместо удаления)
     */
    public function destroy($id)
    {
        $session = Session::findOrFail($id);

        // Проверяем, прошел ли сеанс
        $isPastSession = $session->date_time_session < now();

        if (!$isPastSession) {
            // Для будущих сеансов проверяем наличие активных (не отмененных) бронирований
            $activeBookingsCount = Booking::where('session_id', $session->id_session)
                ->where(function($query) {
                    $query->whereHas('payment', function($q) {
                        $q->where('payment_status', '!=', 'отменено');
                    })
                    ->orWhereDoesntHave('payment');
                })
                ->count();

            if ($activeBookingsCount > 0) {
                return redirect()->route('admin.sessions.index')
                    ->with('error', "Невозможно архивировать сеанс! На данный сеанс есть {$activeBookingsCount} " . 
                        ($activeBookingsCount == 1 ? 'активное бронирование' : ($activeBookingsCount < 5 ? 'активных бронирования' : 'активных бронирований')) . '.');
            }
        }

        // Архивируем сеанс (не удаляем бронирования - они остаются в истории)
        $session->update([
            'is_archived' => true,
        ]);

        $message = $isPastSession 
            ? 'Прошедший сеанс архивирован. Бронирования сохранены в истории.'
            : 'Сеанс архивирован.';

        return redirect()->route('admin.sessions.index')->with('success', $message);
    }
}

