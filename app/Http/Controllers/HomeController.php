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
        // Получаем все жанры для фильтра
        $genres = Genre::orderBy('genre_name', 'asc')->get();

        // Баннеры — первые 5 фильмов из БД, у которых есть баннер
        $bannersQuery = Movie::whereNotNull('baner')
            ->where('baner', '!=', '')
            ->orderBy('id_movie', 'asc')
            ->limit(5)
            ->get();
        
        $banners = collect();
        foreach ($bannersQuery as $movie) {
            // Проверяем, что у фильма есть реальный баннер (не null и не пустая строка)
            if ($movie->baner && trim($movie->baner) !== '') {
                $bannerPath = $this->fixPath($movie->baner, 'images/banners/placeholder.jpg');
                // Проверяем, что баннер не является заглушкой
                if (strpos($bannerPath, 'placeholder') === false) {
                    $banners->push((object)[
                        'movie_title' => $movie->movie_title,
                        'baner' => $bannerPath
                    ]);
                }
            }
        }

        // Если баннеров меньше 5 — добавляем заглушки
        if ($banners->count() < 5) {
            $needed = 5 - $banners->count();
            for ($i = 0; $i < $needed; $i++) {
                $banners->push((object)[
                    'movie_title' => 'Заглушка',
                    'baner' => asset('images/banners/placeholder.jpg')
                ]);
            }
        }

        // Проверяем, применены ли фильтры
        $hasFilters = $request->filled('search') || $request->filled('genre') || 
                      $request->filled('duration_min') || $request->filled('duration_max');

        // Начинаем с запроса всех фильмов
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

        // Получаем фильмы
        $movies = $query->get();

        // Фильтрация по длительности (после получения, т.к. duration хранится как строка)
        if ($request->filled('duration_min') || $request->filled('duration_max')) {
            $minMinutes = null;
            $maxMinutes = null;
            
            // Парсим минимальную длительность
            if ($request->filled('duration_min')) {
                $minDurationInput = trim($request->input('duration_min'));
                $minMinutes = $this->parseDurationInputToMinutes($minDurationInput);
            }
            
            // Парсим максимальную длительность
            if ($request->filled('duration_max')) {
                $maxDurationInput = trim($request->input('duration_max'));
                $maxMinutes = $this->parseDurationInputToMinutes($maxDurationInput);
            }
            
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
        $sortBy = $request->input('sort', 'newest'); // По умолчанию сначала новые
        switch ($sortBy) {
            case 'newest':
                // Сортируем по году выпуска (новые сначала), null значения в конец
                $movies = $movies->sortBy(function($movie) {
                    return $movie->release_year ?? 0;
                }, SORT_REGULAR, true)->values();
                break;
            case 'oldest':
                // Сортируем по году выпуска (старые сначала), null значения в конец
                $movies = $movies->sortBy(function($movie) {
                    return $movie->release_year ?? 9999;
                })->values();
                break;
            default:
                // Сортируем по году выпуска (новые сначала), null значения в конец
                $movies = $movies->sortBy(function($movie) {
                    return $movie->release_year ?? 0;
                }, SORT_REGULAR, true)->values();
        }

        // Обрабатываем пути к постерам и баннерам
        foreach ($movies as $movie) {
            $movie->poster = $this->fixPath($movie->poster, 'images/posters/placeholder.jpg');
            $movie->baner  = $this->fixPath($movie->baner, 'images/banners/placeholder.jpg');
        }

        // Если фильмов нет и применены фильтры — показываем уведомление
        // Баннеры уже сформированы выше (первые 5 фильмов из БД с баннерами)
        if ($movies->isEmpty() && $hasFilters) {
            return view('index', compact('movies', 'banners', 'genres'))->with('no_results', true);
        }

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

    /**
     * Парсит ввод пользователя для длительности в минуты
     * Поддерживает форматы: "1ч. 30 мин", "1ч 30мин", "1:30", "90 мин", "90"
     */
    private function parseDurationInputToMinutes($input)
    {
        if (empty($input)) {
            return null;
        }

        $input = trim($input);
        $minutes = 0;

        // Формат "1ч. 30 мин" или "1ч 30мин" или "1 ч 30 мин" - ищем часы и минуты вместе
        if (preg_match('/(\d+)\s*ч\.?\s*(\d+)\s*мин\.?/u', $input, $matches)) {
            $hours = (int)$matches[1];
            $mins = (int)$matches[2];
            $minutes = $hours * 60 + $mins;
        }
        // Формат "1:30" (часы:минуты)
        elseif (preg_match('/^(\d+):(\d+)$/', $input, $matches)) {
            $hours = (int)$matches[1];
            $mins = (int)$matches[2];
            $minutes = $hours * 60 + $mins;
        }
        // Только часы "2ч" или "2 ч." или "2ч."
        elseif (preg_match('/^(\d+)\s*ч\.?$/u', $input, $matches)) {
            $minutes = (int)$matches[1] * 60;
        }
        // Только минуты "90 мин" или "90 мин." или "90мин"
        elseif (preg_match('/^(\d+)\s*мин\.?$/u', $input, $matches)) {
            $minutes = (int)$matches[1];
        }
        // Просто число - считаем минутами
        elseif (preg_match('/^(\d+)$/', $input, $matches)) {
            $minutes = (int)$matches[1];
        }

        return $minutes > 0 ? $minutes : null;
    }
}
