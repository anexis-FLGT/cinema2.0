<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Session;
use App\Models\Movie;
use Carbon\Carbon;

class SessionController extends Controller
{
    /**
     * Показать страницу со всеми сеансами
     */
    public function index(Request $request)
    {
        // Получаем параметр фильтра по дате
        $selectedDate = $request->input('date');
        
        // Получаем все будущие сеансы с фильмами и залами
        $sessionsQuery = Session::with(['movie', 'hall'])
            ->where('date_time_session', '>=', now());
        
        // Применяем фильтр по дате, если он указан
        if ($selectedDate) {
            $sessionsQuery->whereDate('date_time_session', $selectedDate);
        }
        
        $sessions = $sessionsQuery->orderBy('date_time_session', 'asc')->get();

        // Группируем сеансы по датам
        $sessionsByDate = $sessions->groupBy(function($session) {
            return $session->date_time_session->format('Y-m-d');
        });

        // Группируем сеансы по фильмам
        $sessionsByMovie = $sessions->groupBy('movie_id');

        // Получаем все фильмы, у которых есть сеансы
        $moviesQuery = Movie::whereHas('sessions', function($query) use ($selectedDate) {
            $query->where('date_time_session', '>=', now());
            if ($selectedDate) {
                $query->whereDate('date_time_session', $selectedDate);
            }
        });
        
        $movies = $moviesQuery->with(['genres', 'sessions' => function($query) use ($selectedDate) {
            $query->where('date_time_session', '>=', now());
            if ($selectedDate) {
                $query->whereDate('date_time_session', $selectedDate);
            }
            $query->orderBy('date_time_session', 'asc');
        }])->get();

        // Обрабатываем пути к изображениям для фильмов
        foreach ($movies as $movie) {
            $movie->poster = $this->fixPath($movie->poster, 'images/posters/placeholder.jpg');
            $movie->baner = $this->fixPath($movie->baner, 'images/banners/placeholder.jpg');
        }

        // Получаем список всех доступных дат для фильтра
        $availableDates = Session::where('date_time_session', '>=', now())
            ->selectRaw('DATE(date_time_session) as date')
            ->distinct()
            ->orderBy('date', 'asc')
            ->pluck('date')
            ->map(function($date) {
                return [
                    'value' => $date,
                    'label' => Carbon::parse($date)->locale('ru')->isoFormat('D MMMM YYYY, dddd')
                ];
            });

        return view('sessions.show', compact('sessions', 'sessionsByDate', 'sessionsByMovie', 'movies', 'selectedDate', 'availableDates'));
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

