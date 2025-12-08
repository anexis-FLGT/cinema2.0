<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Movie;
use App\Models\Genre;

class MovieController extends Controller
{
    /**
     * Отображение списка фильмов с пагинацией
     */
    public function index()
    {
        $movies = Movie::with(['genres', 'sessions' => function($query) {
            $query->where('date_time_session', '>', now());
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
            'baner' => 'required|image|mimes:jpg,jpeg,png|max:2048',
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

        // Проверяем наличие активных (будущих) сеансов
        $activeSessionsCount = $movie->sessions()
            ->where('date_time_session', '>', now())
            ->count();

        // Удаляем связи с жанрами
        $movie->genres()->detach();

        // Удаляем все сеансы фильма (включая активные)
        $movie->sessions()->delete();

        // Удаляем фильм
        $movie->delete();

        $message = 'Фильм удалён.';
        if ($activeSessionsCount > 0) {
            $message .= " Также удалено {$activeSessionsCount} активных сеансов.";
        }

        return redirect()->route('admin.movies.index')->with('success', $message);
    }
}
