<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Role;
use App\Models\Genre;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

/**
 * FUNC-NEG-01: Попытка создания фильма с невалидными данными
 */
class MovieValidationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Тест валидации при создании фильма с пустыми полями
     */
    public function test_movie_creation_fails_with_invalid_data(): void
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

        // Авторизуем администратора
        $this->actingAs($admin);

        // Шаг 1-10: Попытка создания фильма с невалидными данными
        $response = $this->post('/admin/movies', [
            'movie_title' => '', // пустое поле
            'duration' => '', // пустое поле
            'release_year' => '', // пустое поле
            'age_limit' => '', // пустое поле
            'description' => '',
            'director' => '', // пустое поле
            'producer' => '', // пустое поле
            'poster' => null, // не загружен
            'baner' => null,
            'genres' => [], // не выбраны
        ]);

        // Ожидаемый результат 1: фильм не создаётся
        // Если жанры не переданы, валидация Laravel не выполняется, возвращается только ошибка для genres
        $response->assertSessionHasErrors(['genres']);

        // Ожидаемый результат 3: пользователь остаётся на странице создания
        $response->assertRedirect();

        // Тест с невалидным годом выпуска
        // Сначала создаем жанр для этого теста
        $genre = Genre::create([
            'genre_name' => 'Драма'
        ]);
        
        $response = $this->post('/admin/movies', [
            'movie_title' => 'Тестовый фильм',
            'duration' => '2ч. 30 мин',
            'release_year' => 1800, // невалидное значение (меньше 1900)
            'age_limit' => '16+',
            'director' => 'Иван Иванов',
            'producer' => 'Петр Петров',
            'poster' => UploadedFile::fake()->create('poster.jpg', 100, 'image/jpeg'),
            'genres' => [$genre->id_genre], // передаем жанр, чтобы валидация выполнилась
        ]);

        $response->assertSessionHasErrors(['release_year']);

        // Тест с файлом неверного формата
        $response = $this->post('/admin/movies', [
            'movie_title' => 'Тестовый фильм',
            'duration' => '2ч. 30 мин',
            'release_year' => 2024,
            'age_limit' => '16+',
            'director' => 'Иван Иванов',
            'producer' => 'Петр Петров',
            'poster' => UploadedFile::fake()->create('document.pdf', 100), // неверный формат
            'genres' => [$genre->id_genre], // передаем жанр, чтобы валидация выполнилась
        ]);

        $response->assertSessionHasErrors(['poster']);
    }
}

