<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

/**
 * FUNC-NEG-02: Попытка пользователя получить доступ к админ-панели
 */
class AdminAccessRestrictionTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Тест ограничения доступа пользователя к админ-панели
     */
    public function test_user_cannot_access_admin_dashboard(): void
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

        // Шаг 1-2: Попытка доступа к админ-панели
        $response = $this->get('/admin/dashboard');

        // Ожидаемый результат 1: запрос отклоняется с кодом 403
        $response->assertStatus(403);

        // Ожидаемый результат 2: отображается сообщение об ошибке
        $response->assertSeeText('У вас недостаточно прав для доступа к этой странице');
    }

    /**
     * Тест ограничения доступа пользователя к управлению фильмами
     */
    public function test_user_cannot_access_admin_movies(): void
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

        // Шаг 1-2: Попытка доступа к управлению фильмами
        $response = $this->get('/admin/movies');

        // Ожидаемый результат 1: запрос отклоняется с кодом 403
        $response->assertStatus(403);

        // Шаг 3: Попытка создания фильма через POST
        $response = $this->post('/admin/movies', [
            'movie_title' => 'Тестовый фильм',
        ]);

        // Ожидаемый результат 1: запрос отклоняется с кодом 403
        $response->assertStatus(403);
    }
}

