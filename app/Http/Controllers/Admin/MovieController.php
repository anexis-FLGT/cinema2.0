<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Movie;
use App\Models\Genre;
use App\Models\Session;
use App\Models\Booking;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;

class MovieController extends Controller
{
    /**
     * Отображение списка фильмов с пагинацией
     */
    public function index()
    {
        $movies = Movie::with(['genres', 'sessions' => function($query) {
            $query->where('is_archived', false)
                  ->where('date_time_session', '>', now());
        }])->paginate(10);
        // Получаем уникальные жанры из pivot таблицы или из таблицы genres
        // Получаем уникальные жанры (если в таблице genres есть movie_id, используем distinct)
        $genres = Genre::select('id_genre', 'genre_name')
            ->distinct()
            ->orderBy('genre_name')
            ->get();

        return view('admin.movies', compact('movies', 'genres'));
    }

    /**
     * Сохранение нового фильма
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'movie_title' => 'required|string|max:255',
            'duration' => 'required|string|max:255',
            'release_year' => 'required|integer|min:1900|max:' . date('Y'),
            'age_limit' => 'required|string|max:10',
            'description' => 'nullable|string',
            'director' => 'required|string|max:255',
            'producer' => 'required|string|max:255',
            'poster' => 'required|image|mimes:jpg,jpeg,png|max:2048',
            'baner' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'genres' => 'required|array|min:1',
            'genres.*' => 'exists:genres,id_genre'
        ]);

        // Загрузка файлов
        $posterPath = $request->file('poster')
            ? '/images/posters/' . $request->file('poster')->hashName()
            : null;
        $banerPath = $request->file('baner')
            ? '/images/baners/' . $request->file('baner')->hashName()
            : null;

        if ($posterPath) $request->file('poster')->move(public_path('images/posters'), basename($posterPath));
        if ($banerPath) $request->file('baner')->move(public_path('images/baners'), basename($banerPath));

        // Обработка описания - убираем лишние пробелы
        $description = $validated['description'] ?? null;
        if ($description) {
            $description = trim($description);
            // Убираем множественные пробелы и переносы строк в начале
            $description = preg_replace('/^\s+/m', '', $description);
            // Нормализуем множественные пробелы
            $description = preg_replace('/[ \t]+/', ' ', $description);
        }

        // Проверка на полностью одинаковый фильм
        $existingMovie = Movie::where('movie_title', $validated['movie_title'])
            ->where('duration', $validated['duration'])
            ->where('release_year', $validated['release_year'])
            ->where('age_limit', $validated['age_limit'])
            ->where('director', $validated['director'])
            ->where('producer', $validated['producer'])
            ->where('description', $description)
            ->first();

        if ($existingMovie) {
            return redirect()->route('admin.movies.index')
                ->with('error', 'Фильм с такими же данными уже существует! Название: ' . $existingMovie->movie_title . '.')
                ->withInput();
        }

        // Создание фильма
        $movie = Movie::create([
            'movie_title' => $validated['movie_title'],
            'duration' => $validated['duration'],
            'release_year' => $validated['release_year'] ?? null,
            'age_limit' => $validated['age_limit'],
            'description' => $description,
            'director' => $validated['director'] ?? null,
            'producer' => $validated['producer'],
            'poster' => $posterPath,
            'baner' => $banerPath,
        ]);

        // Привязка жанров (обязательно)
        $movie->genres()->sync($validated['genres']);

        return redirect()->route('admin.movies.index')->with('success', 'Фильм добавлен.');
    }

    /**
     * Обновление существующего фильма
     */
    public function update(Request $request, $id)
    {
        $movie = Movie::findOrFail($id);

        // Проверяем наличие жанров перед валидацией
        $genres = $request->input('genres', []);
        
        // Если жанры не переданы или пустой массив, возвращаем ошибку
        if (empty($genres) || !is_array($genres) || count(array_filter($genres)) === 0) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['genres' => 'Необходимо выбрать хотя бы один жанр.']);
        }

        $validated = $request->validate([
            'movie_title' => 'required|string|max:255',
            'duration' => 'required|string|max:255',
            'release_year' => 'required|integer|min:1900|max:' . date('Y'),
            'age_limit' => 'required|string|max:10',
            'description' => 'nullable|string',
            'director' => 'required|string|max:255',
            'producer' => 'required|string|max:255',
            'poster' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'baner' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'genres' => 'required|array|min:1',
            'genres.*' => 'exists:genres,id_genre'
        ], [
            'genres.required' => 'Необходимо выбрать хотя бы один жанр.',
            'genres.min' => 'Необходимо выбрать хотя бы один жанр.',
            'genres.*.exists' => 'Выбранный жанр не существует.',
        ]);

        // Загрузка новых файлов при необходимости
        if ($request->hasFile('poster')) {
            $posterPath = '/images/posters/' . $request->file('poster')->hashName();
            $request->file('poster')->move(public_path('images/posters'), basename($posterPath));
            $movie->poster = $posterPath;
        }

        if ($request->hasFile('baner')) {
            $banerPath = '/images/baners/' . $request->file('baner')->hashName();
            $request->file('baner')->move(public_path('images/baners'), basename($banerPath));
            $movie->baner = $banerPath;
        }

        // Обработка описания - убираем лишние пробелы
        $description = $validated['description'] ?? null;
        if ($description) {
            $description = trim($description);
            // Убираем множественные пробелы и переносы строк в начале
            $description = preg_replace('/^\s+/m', '', $description);
            // Нормализуем множественные пробелы
            $description = preg_replace('/[ \t]+/', ' ', $description);
        }

        // Проверка на полностью одинаковый фильм (исключая текущий)
        $existingMovie = Movie::where('movie_title', $validated['movie_title'])
            ->where('duration', $validated['duration'])
            ->where('release_year', $validated['release_year'])
            ->where('age_limit', $validated['age_limit'])
            ->where('director', $validated['director'])
            ->where('producer', $validated['producer'])
            ->where('description', $description)
            ->where('id_movie', '!=', $id)
            ->first();

        if ($existingMovie) {
            return redirect()->route('admin.movies.index')
                ->with('error', 'Фильм с такими же данными уже существует! Название: ' . $existingMovie->movie_title . '.')
                ->withInput();
        }

        // Обновление данных фильма
        $movie->update([
            'movie_title' => $validated['movie_title'],
            'duration' => $validated['duration'],
            'release_year' => $validated['release_year'] ?? null,
            'age_limit' => $validated['age_limit'],
            'description' => $description,
            'director' => $validated['director'] ?? null,
            'producer' => $validated['producer'],
        ]);

        // Синхронизация жанров (обязательно)
        $movie->genres()->sync($validated['genres']);

        return redirect()->route('admin.movies.index')->with('success', 'Фильм обновлён.');
    }

    /**
     * Удаление фильма
     */
    public function destroy($id)
    {
        $movie = Movie::findOrFail($id);

        // Проверяем наличие активных (будущих) неархивированных сеансов
        $activeSessionsCount = $movie->sessions()
            ->where('is_archived', false)
            ->where('date_time_session', '>', now())
            ->count();

        DB::transaction(function () use ($movie) {
            // Получаем все сеансы фильма
            $sessionIds = $movie->sessions()->pluck('id_session');
            
            if ($sessionIds->isNotEmpty()) {
                // Получаем все бронирования этих сеансов
                $bookingIds = Booking::whereIn('session_id', $sessionIds)->pluck('id_booking');
                
                // Удаляем платежи, связанные с этими бронированиями
                if ($bookingIds->isNotEmpty()) {
                    Payment::whereIn('booking_id', $bookingIds)->delete();
                }
                
                // Удаляем бронирования
                Booking::whereIn('session_id', $sessionIds)->delete();
            }
            
            // Удаляем все сеансы фильма
            $movie->sessions()->delete();

            // Удаляем связи с жанрами
            $movie->genres()->detach();

            // Удаляем фильм
            $movie->delete();
        });

        $message = 'Фильм удалён.';
        if ($activeSessionsCount > 0) {
            $message .= " Также удалено {$activeSessionsCount} активных сеансов.";
        }

        return redirect()->route('admin.movies.index')->with('success', $message);
    }
}
