<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

/**
 * FUNC-NEG-03: Попытка пользователя создать фильм (только для администратора)
 */
class UserMovieCreationRestrictionTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Тест ограничения доступа пользователя к созданию фильмов
     */
    public function test_user_cannot_create_movie(): void
    {
        // Предусловия: создаём роль пользователя с role_id = 2
        \DB::table('roles')->insert([
            'id_role' => 2,
            'role_name' => 'Пользователь'
        ]);
        $userRole = Role::find(2);

        // Предусловия: создаём пользователя
        $user = User::create([
            'last_name' => 'Иванов',
            'first_name' => 'Иван',
            'middle_name' => 'Иванович',
            'phone' => '+79991234567',
            'login' => 'testuser',
            'password' => bcrypt('password123'),
            'role_id' => $userRole->id_role,
        ]);

        // Авторизуем пользователя
        $this->actingAs($user);

        // Шаг 1-2: Попытка доступа к странице управления фильмами
        $response = $this->get('/admin/movies');

        // Ожидаемый результат 1: запрос отклоняется с кодом 403
        $response->assertStatus(403);

        // Ожидаемый результат 2: отображается сообщение об ошибке
        $response->assertSeeText('У вас недостаточно прав для доступа к этой странице');

        // Шаг 3: Попытка создания фильма через POST-запрос
        $response = $this->post('/admin/movies', [
            'movie_title' => 'Тестовый фильм',
            'duration' => '2ч. 30 мин',
            'release_year' => 2024,
            'age_limit' => '16+',
            'director' => 'Иван Иванов',
            'producer' => 'Петр Петров',
        ]);

        // Ожидаемый результат 1: запрос отклоняется с кодом 403
        $response->assertStatus(403);

        // Ожидаемый результат 3: пользователь не может создать фильм
        $this->assertDatabaseMissing('movies', [
            'movie_title' => 'Тестовый фильм',
        ]);
    }
}

