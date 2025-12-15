<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Role;
use App\Models\Movie;
use App\Models\Genre;
use App\Models\Hall;
use App\Models\Seat;
use App\Models\Session;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * FUNC-POS-02: Создание бронирования билетов пользователем
 */
class BookingCreationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Тест создания бронирования билетов
     */
    public function test_user_can_create_booking(): void
    {
        // Предусловия: создаём роль пользователя с role_id = 2
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

        $movie->genres()->attach($genre->id_genre);

        // Предусловия: создаём зал
        $hall = Hall::create([
            'hall_name' => 'Зал 1',
            'type_hall' => 'обычный',
            'quantity_seats' => 50,
            'description_hall' => 'Описание зала',
        ]);

        // Предусловия: создаём места в зале
        $seat1 = Seat::create([
            'hall_id' => $hall->id_hall,
            'row_number' => 1,
            'seat_number' => 1,
            'status' => 'Свободно',
        ]);

        $seat2 = Seat::create([
            'hall_id' => $hall->id_hall,
            'row_number' => 1,
            'seat_number' => 2,
            'status' => 'Свободно',
        ]);

        // Предусловия: создаём будущий сеанс
        $session = Session::create([
            'movie_id' => $movie->id_movie,
            'hall_id' => $hall->id_hall,
            'date_time_session' => Carbon::now()->addDays(1)->addHours(2),
            'is_archived' => false,
        ]);

        // Авторизуем пользователя
        $this->actingAs($user);

        // Шаг 1-3: Переход на страницу бронирования
        $response = $this->get("/booking/session/{$session->id_session}");

        // Проверяем, что страница доступна
        $response->assertStatus(200);
        $response->assertViewIs('booking.show');

        // Шаг 4-5: Создание бронирования
        $response = $this->post('/booking', [
            'session_id' => $session->id_session,
            'seat_ids' => [$seat1->id_seat, $seat2->id_seat],
        ]);

        // Ожидаемый результат 1-2: перенаправление на страницу подтверждения
        $response->assertRedirect(route('payment.confirm'));

        // Ожидаемый результат 3: проверяем, что данные сохранены в сессии
        $this->assertTrue(session()->has('pending_booking'));
        $pendingBooking = session('pending_booking');
        $this->assertEquals($session->id_session, $pendingBooking['session_id']);
        $this->assertCount(2, $pendingBooking['seat_ids']);
    }
}

