<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Role;
use App\Models\Movie;
use App\Models\Genre;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

/**
 * FUNC-POS-03: Создание нового фильма администратором
 */
class MovieCreationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Тест создания фильма администратором
     */
    public function test_admin_can_create_movie(): void
    {
        // Предусловия: создаём роль администратора с role_id = 1
        \DB::table('roles')->insert([
            'id_role' => 1,
            'role_name' => 'Администратор'
        ]);
        $adminRole = Role::find(1);

        // Предусловия: создаём администратора
        $admin = User::create([
            'last_name' => 'Админов',
            'first_name' => 'Админ',
            'middle_name' => 'Админович',
            'phone' => '+79991234568',
            'login' => 'admin',
            'password' => bcrypt('admin123'),
            'role_id' => $adminRole->id_role,
        ]);

        // Предусловия: создаём жанр
        $genre = Genre::create([
            'genre_name' => 'Драма'
        ]);

        // Авторизуем администратора
        $this->actingAs($admin);

        // Создаём фейковое хранилище для файлов
        Storage::fake('public');

        // Создаём тестовый файл постера (используем create вместо image для обхода GD)
        $poster = UploadedFile::fake()->create('poster.jpg', 100, 'image/jpeg');

        // Шаг 1-13: Создание фильма
        $response = $this->post('/admin/movies', [
            'movie_title' => 'Тестовый фильм',
            'duration' => '2ч. 30 мин',
            'release_year' => 2024,
            'age_limit' => '16+',
            'description' => 'Описание тестового фильма',
            'director' => 'Иван Иванов',
            'producer' => 'Петр Петров',
            'poster' => $poster,
            'baner' => null,
            'genres' => [$genre->id_genre],
        ]);

        // Ожидаемый результат 1-2: перенаправление на страницу списка фильмов
        $response->assertRedirect(route('admin.movies.index'));

        // Ожидаемый результат 3: проверяем, что фильм создан в базе данных
        $this->assertDatabaseHas('movies', [
            'movie_title' => 'Тестовый фильм',
            'duration' => '2ч. 30 мин',
            'release_year' => 2024,
            'age_limit' => '16+',
            'director' => 'Иван Иванов',
            'producer' => 'Петр Петров',
        ]);

        // Ожидаемый результат 5: проверяем, что фильм привязан к жанру
        $movie = Movie::where('movie_title', 'Тестовый фильм')->first();
        $this->assertNotNull($movie);
        $this->assertTrue($movie->genres->contains('id_genre', $genre->id_genre));
        
        // Проверяем связь в pivot таблице
        $this->assertDatabaseHas('genre_movie', [
            'movie_id' => $movie->id_movie,
            'genre_id' => $genre->id_genre,
        ]);

        // Ожидаемый результат 4: проверяем, что файл сохранён
        // Файл сохраняется в public_path, а не в Storage, поэтому проверяем через file_exists
        $posterPath = public_path($movie->poster);
        $this->assertFileExists($posterPath);
    }
}

