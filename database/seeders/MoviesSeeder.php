<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class MoviesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Список жанров
        $genres = [
            'Драма',
            'Комедия',
            'Триллер',
            'Боевик',
            'Фантастика',
            'Детектив',
            'Ужасы',
            'Мелодрама',
            'Фэнтези',
            'Вестерн',
            'Мультфильм'
        ];

        // Реальные фильмы с реальными данными (6 фильмов)
        $movies = [
            [
                'movie_title' => 'Дюна: Часть вторая',
                'duration' => '2 ч. 46 мин.',
                'duration_minutes' => 166,
                'release_year' => 2024,
                'age_limit' => '12+',
                'description' => 'Продолжение эпической саги о пустынной планете Арракис. Пол Атрейдес объединяется с фременами, чтобы отомстить за свою семью и предотвратить ужасное будущее, которое он видит.',
                'director' => 'Дени Вильнёв',
                'producer' => 'Мэри Пэрент, Кейл Бойтер, Дени Вильнёв',
                'poster' => null,
                'baner' => null,
                'genres' => ['Фантастика', 'Драма', 'Боевик']
            ],
            [
                'movie_title' => 'Оппенгеймер',
                'duration' => '3 ч. 0 мин.',
                'duration_minutes' => 180,
                'release_year' => 2023,
                'age_limit' => '16+',
                'description' => 'История американского физика-теоретика Роберта Оппенгеймера, который руководил Манхэттенским проектом по созданию атомной бомбы во время Второй мировой войны.',
                'director' => 'Кристофер Нолан',
                'producer' => 'Эмма Томас, Чарльз Ровен, Кристофер Нолан',
                'poster' => null,
                'baner' => null,
                'genres' => ['Драма', 'Триллер']
            ],
            [
                'movie_title' => 'Барби',
                'duration' => '1 ч. 54 мин.',
                'duration_minutes' => 114,
                'release_year' => 2023,
                'age_limit' => '12+',
                'description' => 'Барби живет в идеальном мире Барбиленда, пока не обнаруживает, что что-то не так. Она отправляется в реальный мир, чтобы найти ответы.',
                'director' => 'Грета Гервиг',
                'producer' => 'Том Акерли, Робби Бреннер, Дэвид Хейман, Марго Робби',
                'poster' => null,
                'baner' => null,
                'genres' => ['Комедия', 'Фэнтези', 'Мелодрама']
            ],
            [
                'movie_title' => 'Аватар: Путь воды',
                'duration' => '3 ч. 12 мин.',
                'duration_minutes' => 192,
                'release_year' => 2022,
                'age_limit' => '12+',
                'description' => 'Джейк Салли и Нейтири создали семью и делают всё возможное, чтобы оставаться вместе. Однако им приходится покинуть свой дом и исследовать регионы Пандоры.',
                'director' => 'Джеймс Кэмерон',
                'producer' => 'Джеймс Кэмерон, Джон Ландау',
                'poster' => null,
                'baner' => null,
                'genres' => ['Фантастика', 'Боевик', 'Драма']
            ],
            [
                'movie_title' => 'Топ Ган: Мэверик',
                'duration' => '2 ч. 10 мин.',
                'duration_minutes' => 130,
                'release_year' => 2022,
                'age_limit' => '12+',
                'description' => 'Пит "Мэверик" Митчелл возвращается в качестве инструктора пилотов-истребителей, где он обучает новое поколение лучших пилотов для выполнения особой миссии.',
                'director' => 'Джозеф Косински',
                'producer' => 'Джерри Брукхаймер, Том Круз, Кристофер Маккуорри, Дэвид Эллисон',
                'poster' => null,
                'baner' => null,
                'genres' => ['Боевик', 'Драма']
            ],
            [
                'movie_title' => 'Интерстеллар',
                'duration' => '2 ч. 49 мин.',
                'duration_minutes' => 169,
                'release_year' => 2014,
                'age_limit' => '12+',
                'description' => 'Команда исследователей путешествует через червоточину в космосе в попытке обеспечить выживание человечества.',
                'director' => 'Кристофер Нолан',
                'producer' => 'Эмма Томас, Кристофер Нолан, Линда Обст',
                'poster' => null,
                'baner' => null,
                'genres' => ['Фантастика', 'Драма']
            ],
        ];

        // Создаем залы (6 залов)
        $halls = [
            [
                'hall_name' => 'Зал 1',
                'quantity_seats' => '150',
                'type_hall' => 'большой',
                'description_hall' => 'Главный зал кинотеатра с превосходной акустикой и комфортными креслами',
                'hall_photo' => null,
            ],
            [
                'hall_name' => 'Зал 2',
                'quantity_seats' => '120',
                'type_hall' => 'большой',
                'description_hall' => 'Просторный зал с современным оборудованием',
                'hall_photo' => null,
            ],
            [
                'hall_name' => 'Зал 3',
                'quantity_seats' => '100',
                'type_hall' => 'средний',
                'description_hall' => 'Уютный зал среднего размера',
                'hall_photo' => null,
            ],
            [
                'hall_name' => 'Зал 4',
                'quantity_seats' => '80',
                'type_hall' => 'средний',
                'description_hall' => 'Компактный зал для просмотра фильмов',
                'hall_photo' => null,
            ],
            [
                'hall_name' => 'Зал 5',
                'quantity_seats' => '60',
                'type_hall' => 'малый',
                'description_hall' => 'Небольшой VIP-зал с повышенным комфортом',
                'hall_photo' => null,
            ],
            [
                'hall_name' => 'Зал 6',
                'quantity_seats' => '50',
                'type_hall' => 'малый',
                'description_hall' => 'Мини-зал для камерных просмотров',
                'hall_photo' => null,
            ],
        ];

        // Создаем залы
        $hallIds = [];
        foreach ($halls as $hallData) {
            $hallId = DB::table('halls')->insertGetId($hallData);
            $hallIds[] = $hallId;
        }

        // Создаем первый фильм для инициализации жанров (если нужно)
        $firstMovieId = null;
        if (count($movies) > 0) {
            $firstMovieData = $movies[0];
            $firstMovieGenres = $firstMovieData['genres'];
            unset($firstMovieData['genres']);
            unset($firstMovieData['duration_minutes']); // Удаляем временное поле
            $firstMovieId = DB::table('movies')->insertGetId($firstMovieData);
        }

        // Создаем жанры (если их еще нет)
        $genreIds = [];
        foreach ($genres as $genreName) {
            // Проверяем, существует ли жанр
            $genre = DB::table('genres')->where('genre_name', $genreName)->first();
            if (!$genre) {
                // Создаем жанр (без movie_id, так как связь только через genre_movie)
                $genreId = DB::table('genres')->insertGetId([
                    'genre_name' => $genreName,
                ]);
                $genreIds[$genreName] = $genreId;
            } else {
                $genreIds[$genreName] = $genre->id_genre;
            }
        }

        // Связываем первый фильм с его жанрами через genre_movie
        if ($firstMovieId && isset($firstMovieGenres)) {
            foreach ($firstMovieGenres as $genreName) {
                if (isset($genreIds[$genreName])) {
                    $exists = DB::table('genre_movie')
                        ->where('movie_id', $firstMovieId)
                        ->where('genre_id', $genreIds[$genreName])
                        ->exists();

                    if (!$exists) {
                        DB::table('genre_movie')->insert([
                            'movie_id' => $firstMovieId,
                            'genre_id' => $genreIds[$genreName],
                        ]);
                    }
                }
            }

            // Создаем сеансы для первого фильма
            $this->createSessionsForMovie($firstMovieId, $hallIds[0], $movies[0]['duration_minutes'], 0);
        }

        // Вставляем остальные фильмы
        for ($i = 1; $i < count($movies); $i++) {
            $movieData = $movies[$i];
            $genresForMovie = $movieData['genres'];
            $durationMinutes = $movieData['duration_minutes'];
            unset($movieData['genres']);
            unset($movieData['duration_minutes']); // Удаляем временное поле

            $movieId = DB::table('movies')->insertGetId($movieData);

            // Связываем фильм с жанрами через genre_movie
            foreach ($genresForMovie as $genreName) {
                if (isset($genreIds[$genreName])) {
                    // Проверяем, не существует ли уже такая связь
                    $exists = DB::table('genre_movie')
                        ->where('movie_id', $movieId)
                        ->where('genre_id', $genreIds[$genreName])
                        ->exists();

                    if (!$exists) {
                        DB::table('genre_movie')->insert([
                            'movie_id' => $movieId,
                            'genre_id' => $genreIds[$genreName],
                        ]);
                    }
                }
            }

            // Создаем сеансы для фильма (распределяем по залам)
            $hallIndex = $i % count($hallIds);
            $this->createSessionsForMovie($movieId, $hallIds[$hallIndex], $durationMinutes, $i);
        }
    }

    /**
     * Создает сеансы для фильма с 20 по 22 декабря 2025 года
     * Учитывает длительность фильма и время на уборку между сеансами
     */
    private function createSessionsForMovie($movieId, $hallId, $durationMinutes, $movieIndex): void
    {
        // Время на уборку зала между сеансами (в минутах)
        $cleaningTime = 20;
        
        // Общее время на сеанс (фильм + уборка)
        $totalSessionTime = $durationMinutes + $cleaningTime;
        
        // Создаем сеансы с 20 по 22 декабря 2025 года
        $startDate = Carbon::create(2025, 12, 20, 0, 0, 0);
        $endDate = Carbon::create(2025, 12, 22, 23, 59, 59);

        $currentDate = $startDate->copy();

        // Расписание сеансов для каждого дня (3 сеанса)
        // Распределяем фильмы по разным временным слотам, чтобы избежать конфликтов
        $timeSlots = [
            [13, 0],   // 13:00
            [16, 30],  // 16:30
            [20, 0],   // 20:00
        ];
        
        // Смещение для разных фильмов (чтобы не было конфликтов в одном зале)
        $timeOffset = $movieIndex * 15; // Смещение в минутах

        while ($currentDate->lte($endDate)) {
            foreach ($timeSlots as $slot) {
                $sessionDateTime = $currentDate->copy()
                    ->setTime($slot[0], $slot[1])
                    ->addMinutes($timeOffset);
                
                // Проверяем, не выходит ли сеанс за разумные пределы (до 23:00)
                if ($sessionDateTime->hour < 23) {
                    // Проверяем, не существует ли уже такой сеанс в этом зале
                    $exists = DB::table('cinema_sessions')
                        ->where('hall_id', $hallId)
                        ->where('date_time_session', $sessionDateTime->format('Y-m-d H:i:s'))
                        ->exists();

                    if (!$exists) {
                        DB::table('cinema_sessions')->insert([
                            'movie_id' => $movieId,
                            'hall_id' => $hallId,
                            'date_time_session' => $sessionDateTime,
                            'is_archived' => false,
                        ]);
                    }
                }
            }

            $currentDate->addDay();
        }
    }
}

