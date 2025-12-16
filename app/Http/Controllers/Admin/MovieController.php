<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Movie;
use App\Models\Genre;
use App\Models\Session;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\Director;
use App\Models\Producer;
use Illuminate\Support\Facades\DB;

class MovieController extends Controller
{
    /**
     * Отображение списка фильмов с пагинацией, поиском и фильтрацией
     */
    public function index(Request $request)
    {
        $query = Movie::with(['genres', 'sessions' => function($query) {
            $query->where('is_archived', false)
                  ->where('date_time_session', '>', now());
        }]);

        // Поиск по названию
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where('movie_title', 'like', "%{$search}%");
        }

        // Фильтрация по жанру
        if ($request->filled('genre_id')) {
            $query->whereHas('genres', function($q) use ($request) {
                $q->where('genres.id_genre', $request->input('genre_id'));
            });
        }

        $movies = $query->orderBy('id_movie', 'desc')
            ->paginate(10)
            ->withQueryString();

        // Получаем уникальные жанры
        $genres = Genre::select('id_genre', 'genre_name')
            ->distinct()
            ->orderBy('genre_name')
            ->get();

        // Справочники режиссёров и продюсеров
        $allDirectors = Director::orderBy('name')->get();
        $allProducers = Producer::orderBy('name')->get();

        return view('admin.movies', compact('movies', 'genres', 'allDirectors', 'allProducers'));
    }

    /**
     * Сохранение нового фильма
     */
    public function store(Request $request)
    {
        // Проверяем наличие жанров перед валидацией
        $genres = $request->input('genres', []);
        
        // Если жанры не переданы или пустой массив, возвращаем ошибку
        if (empty($genres) || !is_array($genres) || count(array_filter($genres)) === 0) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['genres' => 'Необходимо выбрать хотя бы один жанр.']);
        }

        // Валидация файлов с проверкой расширения
        if ($request->hasFile('poster')) {
            $posterExtension = strtolower($request->file('poster')->getClientOriginalExtension());
            $allowedExtensions = ['jpg', 'jpeg', 'png'];
            if (!in_array($posterExtension, $allowedExtensions)) {
                return redirect()->back()
                    ->withInput()
                    ->withErrors(['poster' => 'Постер должен быть в формате JPG, JPEG или PNG.']);
            }
        }

        if ($request->hasFile('baner')) {
            $banerExtension = strtolower($request->file('baner')->getClientOriginalExtension());
            $allowedExtensions = ['jpg', 'jpeg', 'png'];
            if (!in_array($banerExtension, $allowedExtensions)) {
                return redirect()->back()
                    ->withInput()
                    ->withErrors(['baner' => 'Баннер должен быть в формате JPG, JPEG или PNG.']);
            }
        }

        $validated = $request->validate([
            'movie_title' => 'required|string|max:255',
            'duration' => 'required|string|max:255',
            'release_year' => 'required|integer|min:1900|max:' . date('Y'),
            'age_limit' => 'required|string|max:10',
            'description' => 'nullable|string',
            'directors' => 'array',
            'directors.*' => 'integer|exists:directors,id_director',
            'new_directors' => 'nullable|string|max:500',
            'producers' => 'array',
            'producers.*' => 'integer|exists:producers,id_producer',
            'new_producers' => 'nullable|string|max:500',
            'poster' => 'required|image|max:2048',
            'baner' => 'nullable|image|max:2048',
            'genres' => 'required|array|min:1',
            'genres.*' => 'exists:genres,id_genre'
        ], [
            'genres.required' => 'Необходимо выбрать хотя бы один жанр.',
            'genres.min' => 'Необходимо выбрать хотя бы один жанр.',
            'genres.*.exists' => 'Выбранный жанр не существует.',
            'poster.required' => 'Постер обязателен для загрузки.',
            'poster.image' => 'Постер должен быть изображением.',
            'poster.max' => 'Размер постера не должен превышать 2 МБ.',
            'baner.image' => 'Баннер должен быть изображением.',
            'baner.max' => 'Размер баннера не должен превышать 2 МБ.',
        ]);

        // Загрузка файлов
        $posterPath = null;
        $banerPath = null;

        if ($request->hasFile('poster')) {
            // Создаем директорию, если её нет
            $imagesDir = public_path('images');
            $postersDir = public_path('images/posters');
            if (!file_exists($imagesDir)) {
                mkdir($imagesDir, 0755, true);
            }
            if (!file_exists($postersDir)) {
                mkdir($postersDir, 0755, true);
            }
            
            $posterPath = '/images/posters/' . $request->file('poster')->hashName();
            $request->file('poster')->move($postersDir, basename($posterPath));
        }

        if ($request->hasFile('baner')) {
            // Создаем директорию, если её нет
            $imagesDir = public_path('images');
            $banersDir = public_path('images/baners');
            if (!file_exists($imagesDir)) {
                mkdir($imagesDir, 0755, true);
            }
            if (!file_exists($banersDir)) {
                mkdir($banersDir, 0755, true);
            }
            
            $banerPath = '/images/baners/' . $request->file('baner')->hashName();
            $request->file('baner')->move($banersDir, basename($banerPath));
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

        // Проверяем, что указан хотя бы один режиссёр и продюсер
        $hasDirectors = !empty($validated['directors'] ?? []) || !empty(trim($validated['new_directors'] ?? ''));
        $hasProducers = !empty($validated['producers'] ?? []) || !empty(trim($validated['new_producers'] ?? ''));

        if (!$hasDirectors) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['directors' => 'Укажите хотя бы одного режиссёра.']);
        }

        if (!$hasProducers) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['producers' => 'Укажите хотя бы одного продюсера.']);
        }

        // Проверка на полностью одинаковый фильм
        $existingMovie = Movie::where('movie_title', $validated['movie_title'])
            ->where('duration', $validated['duration'])
            ->where('release_year', $validated['release_year'])
            ->where('age_limit', $validated['age_limit'])
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
            'poster' => $posterPath,
            'baner' => $banerPath,
        ]);

        // Привязка жанров (обязательно)
        $movie->genres()->sync($validated['genres']);

        // Привязка режиссёров и продюсеров
        $this->syncPeople($movie, $validated['directors'] ?? [], $validated['new_directors'] ?? '', $validated['producers'] ?? [], $validated['new_producers'] ?? '');

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
            'directors' => 'array',
            'directors.*' => 'integer|exists:directors,id_director',
            'new_directors' => 'nullable|string|max:500',
            'producers' => 'array',
            'producers.*' => 'integer|exists:producers,id_producer',
            'new_producers' => 'nullable|string|max:500',
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
            ->where('description', $description)
            ->where('id_movie', '!=', $id)
            ->first();

        if ($existingMovie) {
            return redirect()->route('admin.movies.index')
                ->with('error', 'Фильм с такими же данными уже существует! Название: ' . $existingMovie->movie_title . '.')
                ->withInput();
        }

        // Проверяем, что указан хотя бы один режиссёр и продюсер
        $hasDirectors = !empty($validated['directors'] ?? []) || !empty(trim($validated['new_directors'] ?? ''));
        $hasProducers = !empty($validated['producers'] ?? []) || !empty(trim($validated['new_producers'] ?? ''));

        if (!$hasDirectors) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['directors' => 'Укажите хотя бы одного режиссёра.']);
        }

        if (!$hasProducers) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['producers' => 'Укажите хотя бы одного продюсера.']);
        }

        // Обновление данных фильма
        $movie->update([
            'movie_title' => $validated['movie_title'],
            'duration' => $validated['duration'],
            'release_year' => $validated['release_year'] ?? null,
            'age_limit' => $validated['age_limit'],
            'description' => $description,
        ]);

        // Синхронизация жанров (обязательно)
        $movie->genres()->sync($validated['genres']);

        // Синхронизация режиссёров и продюсеров
        $this->syncPeople($movie, $validated['directors'] ?? [], $validated['new_directors'] ?? '', $validated['producers'] ?? [], $validated['new_producers'] ?? '');

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

    /**
     * Привязка режиссёров и продюсеров к фильму (many-to-many)
     */
    protected function syncPeople(Movie $movie, array $directorIds, string $newDirectors, array $producerIds, string $newProducers): void
    {
        // Режиссёры
        $directorIds = collect($directorIds)->filter()->map(fn ($id) => (int) $id)->values();

        $newDirectorNames = $this->splitNames($newDirectors);
        foreach ($newDirectorNames as $name) {
            $dir = Director::firstOrCreate(['name' => $name]);
            $directorIds->push($dir->id_director);
        }

        $movie->directors()->sync($directorIds->unique()->values());

        // Продюсеры
        $producerIds = collect($producerIds)->filter()->map(fn ($id) => (int) $id)->values();

        $newProducerNames = $this->splitNames($newProducers);
        foreach ($newProducerNames as $name) {
            $prod = Producer::firstOrCreate(['name' => $name]);
            $producerIds->push($prod->id_producer);
        }

        $movie->producers()->sync($producerIds->unique()->values());
    }

    protected function splitNames(?string $raw): array
    {
        if (!$raw) {
            return [];
        }
        $parts = preg_split('/[;,]+/', $raw);
        return collect($parts)
            ->map(fn ($v) => trim($v))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }
}
