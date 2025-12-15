<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Role;
use App\Models\Movie;
use App\Models\Genre;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\DB;

/**
 * FUNC-POS-01: Успешная авторизация пользователя и просмотр списка фильмов
 */
class LoginAndViewMoviesTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Тест успешной авторизации и просмотра списка фильмов
     */
    public function test_user_can_login_and_view_movies_list(): void
    {
        // Предусловия: создаём роль пользователя с id_role = 2
        \DB::table('roles')->insert([
            'id_role' => 2,
            'role_name' => 'Пользователь'
        ]);
        $role = Role::find(2);

        // Предусловия: создаём пользователя
        $user = User::create([
            'last_name' => 'Иванов',
            'first_name' => 'Иван',
            'middle_name' => 'Иванович',
            'phone' => '+79991234567',
            'login' => 'testuser',
            'password' => bcrypt('password123'),
            'role_id' => $role->id_role,
        ]);

        // Предусловия: создаём жанр
        $genre = Genre::create([
            'genre_name' => 'Драма'
        ]);

        // Предусловия: создаём фильм
        $movie = Movie::create([
            'movie_title' => 'Тестовый фильм',
            'duration' => '2ч. 30 мин',
            'release_year' => 2024,
            'age_limit' => '16+',
            'description' => 'Описание тестового фильма',
            'director' => 'Иван Иванов',
            'producer' => 'Петр Петров',
            'poster' => 'images/posters/test.jpg',
            'baner' => null,
        ]);

        // Привязываем жанр к фильму
        $movie->genres()->attach($genre->id_genre);

        // Шаг 1-4: Авторизация
        $response = $this->post('/login', [
            'login' => 'testuser',
            'password' => 'password123',
        ]);

        // Ожидаемый результат 1: пользователь успешно авторизован
        $this->assertAuthenticatedAs($user);

        // Ожидаемый результат 2: происходит перенаправление на главную страницу
        $response->assertRedirect('/');

        // Шаг 5-6: Просмотр списка фильмов
        $response = $this->get('/');

        // Ожидаемый результат 3: отображается список фильмов
        $response->assertStatus(200);
        $response->assertViewIs('index');
        $response->assertViewHas('movies');
        
        // Проверяем, что фильм присутствует в списке
        $movies = $response->viewData('movies');
        $this->assertTrue($movies->contains('id_movie', $movie->id_movie));
    }
}

