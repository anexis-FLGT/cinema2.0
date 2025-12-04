<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Movie;
use App\Models\Genre;

class HomeController extends Controller
{
    /**
     * Главная страница — афиша фильмов.
     */
    public function index(Request $request)
    {
        // Получаем все фильмы с жанрами и сеансами
        $query = Movie::with(['genres', 'sessions']);

        // Поиск по названию
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where('movie_title', 'like', "%{$search}%");
        }

        // Фильтрация по жанру
        if ($request->filled('genre')) {
            $genreId = $request->input('genre');
            $query->whereHas('genres', function($q) use ($genreId) {
                $q->where('genres.id_genre', $genreId);
            });
        }

        // Фильтрация по дате показа
        if ($request->filled('show_date')) {
            $showDate = $request->input('show_date');
            $query->whereHas('sessions', function($q) use ($showDate) {
                $q->whereDate('date_time_session', $showDate);
            });
        }

        // Получаем фильмы
        $movies = $query->get();

        // Фильтрация по длительности (после получения, т.к. duration хранится как строка)
        if ($request->filled('duration_min') || $request->filled('duration_max')) {
            $minMinutes = $request->filled('duration_min') ? (int)$request->input('duration_min') : null;
            $maxMinutes = $request->filled('duration_max') ? (int)$request->input('duration_max') : null;
            
            $movies = $movies->filter(function($movie) use ($minMinutes, $maxMinutes) {
                $minutes = $this->parseDurationToMinutes($movie->duration);
                
                if ($minMinutes !== null && $minutes < $minMinutes) {
                    return false;
                }
                
                if ($maxMinutes !== null && $minutes > $maxMinutes) {
                    return false;
                }
                
                return true;
            })->values();
        }

        // Сортировка (применяем к коллекции)
        $sortBy = $request->input('sort', 'alphabet'); // По умолчанию по алфавиту
        switch ($sortBy) {
            case 'alphabet':
                $movies = $movies->sortBy('movie_title')->values();
                break;
            case 'alphabet_desc':
                $movies = $movies->sortByDesc('movie_title')->values();
                break;
            case 'newest':
                $movies = $movies->sortByDesc('id_movie')->values();
                break;
            case 'oldest':
                $movies = $movies->sortBy('id_movie')->values();
                break;
            default:
                $movies = $movies->sortBy('movie_title')->values();
        }

        // Обрабатываем пути к постерам и баннерам
        foreach ($movies as $movie) {
            $movie->poster = $this->fixPath($movie->poster, 'images/posters/placeholder.jpg');
            $movie->baner  = $this->fixPath($movie->baner, 'images/banners/placeholder.jpg');
        }

        // Баннеры — первые 3 фильма
        $banners = $movies->take(3);

        // Если баннеров меньше 3 — добавляем заглушки
        if ($banners->count() < 3) {
            $needed = 3 - $banners->count();
            for ($i = 0; $i < $needed; $i++) {
                $banners->push((object)[
                    'movie_title' => 'Заглушка',
                    'baner' => asset('images/banners/placeholder.jpg')
                ]);
            }
        }

        // Если фильмов нет — создаём заглушки
        if ($movies->isEmpty()) {
            $movies = collect();
            for ($i = 1; $i <= 8; $i++) {
                $movies->push((object)[
                    'id_movie' => $i,
                    'movie_title' => 'Заглушка',
                    'poster' => asset('images/posters/placeholder.jpg'),
                    'baner' => asset('images/banners/placeholder.jpg'),
                    'age_limit' => '0+',
                    'duration' => '0 мин',
                    'producer' => 'Неизвестен',
                    'genre' => 'Жанр не указан',
                ]);
            }
        }

        // Получаем все жанры для фильтра
        $genres = Genre::orderBy('genre_name', 'asc')->get();

        return view('index', compact('movies', 'banners', 'genres'));
    }

    /**
     * Страница конкретного фильма.
     */
    public function showMovie($id)
    {
        // Загружаем фильм вместе с жанрами (через pivot genre_movie)
        $movie = Movie::with('genres')->find($id);

        // Если фильм не найден — выводим заглушку
        if (!$movie) {
            $movie = (object)[
                'id_movie' => 0,
                'movie_title' => 'Фильм не найден',
                'poster' => asset('images/posters/placeholder.jpg'),
                'baner' => asset('images/banners/placeholder.jpg'),
                'description' => 'Описание временно недоступно.',
                'producer' => 'Неизвестен',
                'duration' => '0 мин',
                'age_limit' => '0+',
                'genres' => collect(),
            ];
        } else {
            $movie->poster = $this->fixPath($movie->poster, 'images/posters/placeholder.jpg');
            $movie->baner  = $this->fixPath($movie->baner, 'images/banners/placeholder.jpg');
        }

        return view('movie.show', compact('movie'));
    }

    /**
     * Исправление путей (если null — подставляем заглушку).
     */
    private function fixPath($path, $placeholder)
    {
        if (!$path) return asset($placeholder);
        return asset(ltrim(str_replace('\\', '/', $path), '/'));
    }

    /**
     * Парсит длительность из строки в минуты
     * Например: "1 ч. 30 мин." -> 90
     */
    private function parseDurationToMinutes($duration)
    {
        if (empty($duration)) {
            return 0;
        }

        $minutes = 0;
        
        // Ищем часы
        if (preg_match('/(\d+)\s*ч\.?/u', $duration, $matches)) {
            $minutes += (int)$matches[1] * 60;
        }
        
        // Ищем минуты
        if (preg_match('/(\d+)\s*мин\.?/u', $duration, $matches)) {
            $minutes += (int)$matches[1];
        }
        
        // Если только число без единиц измерения, считаем это минутами
        if ($minutes == 0 && preg_match('/^(\d+)$/', $duration, $matches)) {
            $minutes = (int)$matches[1];
        }

        return $minutes;
    }
}
