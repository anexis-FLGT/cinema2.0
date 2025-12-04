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
        $movies = Movie::with('genres')->paginate(10);
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
            'age_limit' => 'required|string|max:10',
            'description' => 'required|string',
            'producer' => 'required|string|max:255',
            'poster' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'baner' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'genres' => 'array'
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

        // Создание фильма
        $movie = Movie::create([
            'movie_title' => $validated['movie_title'],
            'duration' => $validated['duration'],
            'age_limit' => $validated['age_limit'],
            'description' => $validated['description'],
            'producer' => $validated['producer'],
            'poster' => $posterPath,
            'baner' => $banerPath,
        ]);

        // Привязка жанров
        if (!empty($validated['genres'])) {
            $movie->genres()->sync($validated['genres']);
        }

        return redirect()->route('admin.movies.index')->with('success', 'Фильм добавлен.');
    }

    /**
     * Обновление существующего фильма
     */
    public function update(Request $request, $id)
    {
        $movie = Movie::findOrFail($id);

        $validated = $request->validate([
            'movie_title' => 'required|string|max:255',
            'duration' => 'required|string|max:255',
            'age_limit' => 'required|string|max:10',
            'description' => 'required|string',
            'producer' => 'required|string|max:255',
            'poster' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'baner' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'genres' => 'array'
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

        // Обновление данных фильма
        $movie->update([
            'movie_title' => $validated['movie_title'],
            'duration' => $validated['duration'],
            'age_limit' => $validated['age_limit'],
            'description' => $validated['description'],
            'producer' => $validated['producer'],
        ]);

        // Синхронизация жанров
        if (!empty($validated['genres'])) {
            $movie->genres()->sync($validated['genres']);
        } else {
            $movie->genres()->detach();
        }

        return redirect()->route('admin.movies.index')->with('success', 'Фильм обновлён.');
    }

    /**
     * Удаление фильма
     */
    public function destroy($id)
    {
        $movie = Movie::findOrFail($id);

        // Удаляем связи с жанрами
        $movie->genres()->detach();

        // Удаляем фильм
        $movie->delete();

        return redirect()->route('admin.movies.index')->with('success', 'Фильм удалён.');
    }
}
