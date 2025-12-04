<?php

namespace App\Http\Controllers;

use App\Models\Session;
use App\Models\Movie;
use Carbon\Carbon;

class SessionController extends Controller
{
    /**
     * Показать страницу со всеми сеансами
     */
    public function index()
    {
        // Получаем все будущие сеансы с фильмами и залами
        $sessions = Session::with(['movie', 'hall'])
            ->where('date_time_session', '>=', now())
            ->orderBy('date_time_session', 'asc')
            ->get();

        // Группируем сеансы по датам
        $sessionsByDate = $sessions->groupBy(function($session) {
            return $session->date_time_session->format('Y-m-d');
        });

        // Группируем сеансы по фильмам
        $sessionsByMovie = $sessions->groupBy('movie_id');

        // Получаем все фильмы, у которых есть сеансы
        $movies = Movie::whereHas('sessions', function($query) {
            $query->where('date_time_session', '>=', now());
        })
        ->with(['genres', 'sessions' => function($query) {
            $query->where('date_time_session', '>=', now())
                  ->orderBy('date_time_session', 'asc');
        }])
        ->get();

        // Обрабатываем пути к изображениям для фильмов
        foreach ($movies as $movie) {
            $movie->poster = $this->fixPath($movie->poster, 'images/posters/placeholder.jpg');
            $movie->baner = $this->fixPath($movie->baner, 'images/banners/placeholder.jpg');
        }

        return view('sessions.show', compact('sessions', 'sessionsByDate', 'sessionsByMovie', 'movies'));
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

