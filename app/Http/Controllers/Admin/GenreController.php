<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Genre;

class GenreController extends Controller
{
    /**
     * Отображение списка жанров
     */
    public function index()
    {
        $genres = Genre::withCount('movies')
            ->orderBy('genre_name')
            ->paginate(10);
        return view('admin.genres', compact('genres'));
    }

    /**
     * Сохранение нового жанра
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'genre_name' => 'required|string|max:255|unique:genres,genre_name',
        ], [
            'genre_name.required' => 'Название жанра обязательно для заполнения.',
            'genre_name.unique' => 'Жанр с таким названием уже существует.',
        ]);

        Genre::create([
            'genre_name' => $validated['genre_name'],
        ]);

        return redirect()->route('admin.genres.index')->with('success', 'Жанр добавлен.');
    }

    /**
     * Обновление существующего жанра
     */
    public function update(Request $request, $id)
    {
        $genre = Genre::findOrFail($id);

        $validated = $request->validate([
            'genre_name' => 'required|string|max:255|unique:genres,genre_name,' . $id . ',id_genre',
        ], [
            'genre_name.required' => 'Название жанра обязательно для заполнения.',
            'genre_name.unique' => 'Жанр с таким названием уже существует.',
        ]);

        $genre->update([
            'genre_name' => $validated['genre_name'],
        ]);

        return redirect()->route('admin.genres.index')->with('success', 'Жанр обновлён.');
    }

    /**
     * Удаление жанра
     */
    public function destroy($id)
    {
        $genre = Genre::findOrFail($id);

        // Проверяем, используется ли жанр в фильмах
        $moviesCount = $genre->movies()->count();

        if ($moviesCount > 0) {
            return redirect()->route('admin.genres.index')
                ->with('error', "Невозможно удалить жанр. Он используется в {$moviesCount} фильмах.");
        }

        $genre->delete();

        return redirect()->route('admin.genres.index')->with('success', 'Жанр удалён.');
    }
}


