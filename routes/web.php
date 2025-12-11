<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\HallController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\MovieController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\GenreController;
use App\Http\Controllers\User\UserController as UserUserController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\PaymentController;

/*
|-------------------------------------------------------------------------- 
| Главная
|-------------------------------------------------------------------------- 
*/
Route::get('/', [HomeController::class, 'index'])->name('home');

/*
|-------------------------------------------------------------------------- 
| Регистрация
|-------------------------------------------------------------------------- 
*/
Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [RegisterController::class, 'register'])->name('register.post');

/*
|-------------------------------------------------------------------------- 
| Авторизация
|-------------------------------------------------------------------------- 
*/
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.post');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

/*
|-------------------------------------------------------------------------- 
| Контакты и просмотр фильма (доступ открытый)
|-------------------------------------------------------------------------- 
*/
Route::get('/contacts', function () {
    return view('contacts');
})->name('contacts');

Route::get('/movie/{id}', [HomeController::class, 'showMovie'])->name('movie.show');

/*
|-------------------------------------------------------------------------- 
| Залы (доступ открытый)
|-------------------------------------------------------------------------- 
*/
Route::get('/halls', [HallController::class, 'index'])->name('halls');

/*
|-------------------------------------------------------------------------- 
| Сеансы (доступ открытый)
|-------------------------------------------------------------------------- 
*/
Route::get('/sessions', [\App\Http\Controllers\SessionController::class, 'index'])->name('sessions');

/*
|-------------------------------------------------------------------------- 
| Бронирование билетов
|-------------------------------------------------------------------------- 
*/
Route::get('/booking/session/{sessionId}', [BookingController::class, 'show'])->name('booking.show');
Route::post('/booking/get-hall-seats', [BookingController::class, 'getHallSeats'])->name('booking.getHallSeats');
Route::post('/booking', [BookingController::class, 'store'])->name('booking.store');
Route::get('/booking/success/{bookingId}', [BookingController::class, 'success'])->name('booking.success');

/*
|-------------------------------------------------------------------------- 
| Оплата через ЮKassa
|-------------------------------------------------------------------------- 
*/
Route::get('/payment/confirm', [PaymentController::class, 'confirm'])->name('payment.confirm');
Route::post('/payment/create', [PaymentController::class, 'create'])->name('payment.create');
Route::get('/payment/success', [PaymentController::class, 'success'])->name('payment.success');
Route::post('/payment/webhook', [PaymentController::class, 'webhook'])->name('payment.webhook');
Route::get('/payment/retry/{bookingId}', [PaymentController::class, 'retryPayment'])->name('payment.retry')->middleware('auth');

/*
|-------------------------------------------------------------------------- 
| Панель администратора (только для role_id = 1)
|-------------------------------------------------------------------------- 
*/
Route::prefix('admin')->name('admin.')->middleware(['auth', 'role:1'])->group(function () {

    // Главная админки
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');

    // Раздел фильмов
    Route::get('/movies', [MovieController::class, 'index'])->name('movies.index');
    Route::post('/movies', [MovieController::class, 'store'])->name('movies.store');
    Route::put('/movies/{id}', [MovieController::class, 'update'])->name('movies.update');
    Route::delete('/movies/{id}', [MovieController::class, 'destroy'])->name('movies.destroy');

    // Раздел сеансов
    Route::get('/sessions', [\App\Http\Controllers\Admin\SessionController::class, 'index'])->name('sessions.index');
    Route::post('/sessions', [\App\Http\Controllers\Admin\SessionController::class, 'store'])->name('sessions.store');
    Route::put('/sessions/{id}', [\App\Http\Controllers\Admin\SessionController::class, 'update'])->name('sessions.update');
    Route::delete('/sessions/{id}', [\App\Http\Controllers\Admin\SessionController::class, 'destroy'])->name('sessions.destroy');

    // Раздел пользователей (CRUD)
    Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
    Route::post('/users', [AdminUserController::class, 'store'])->name('users.store');
    Route::put('/users/{id}', [AdminUserController::class, 'update'])->name('users.update');
    Route::delete('/users/{id}', [AdminUserController::class, 'destroy'])->name('users.destroy');

    // AJAX подгрузка пользователей
    Route::get('/users/list', [AdminController::class, 'usersList'])->name('users.list');

    // Раздел жанров (CRUD)
    Route::get('/genres', [GenreController::class, 'index'])->name('genres.index');
    Route::post('/genres', [GenreController::class, 'store'])->name('genres.store');
    Route::put('/genres/{id}', [GenreController::class, 'update'])->name('genres.update');
    Route::delete('/genres/{id}', [GenreController::class, 'destroy'])->name('genres.destroy');

    // Раздел отчетов
    Route::get('/reports', [\App\Http\Controllers\Admin\ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/revenue', [\App\Http\Controllers\Admin\ReportController::class, 'revenue'])->name('reports.revenue');
    Route::get('/reports/attendance', [\App\Http\Controllers\Admin\ReportController::class, 'attendance'])->name('reports.attendance');
    Route::get('/reports/movies', [\App\Http\Controllers\Admin\ReportController::class, 'movies'])->name('reports.movies');
    
    // Экспорт отчетов в PDF
    Route::get('/reports/revenue/pdf', [\App\Http\Controllers\Admin\ReportController::class, 'revenuePdf'])->name('reports.revenue.pdf');
    Route::get('/reports/attendance/pdf', [\App\Http\Controllers\Admin\ReportController::class, 'attendancePdf'])->name('reports.attendance.pdf');
    Route::get('/reports/movies/pdf', [\App\Http\Controllers\Admin\ReportController::class, 'moviesPdf'])->name('reports.movies.pdf');

    // Раздел истории операций
    Route::get('/history', [\App\Http\Controllers\Admin\HistoryController::class, 'index'])->name('history.index');
    Route::get('/history/{id}', [\App\Http\Controllers\Admin\HistoryController::class, 'show'])->name('history.show');

    // Раздел залов (CRUD)
    Route::get('/halls', [\App\Http\Controllers\Admin\HallController::class, 'index'])->name('halls.index');
    Route::post('/halls', [\App\Http\Controllers\Admin\HallController::class, 'store'])->name('halls.store');
    Route::put('/halls/{id}', [\App\Http\Controllers\Admin\HallController::class, 'update'])->name('halls.update');
    Route::delete('/halls/{id}', [\App\Http\Controllers\Admin\HallController::class, 'destroy'])->name('halls.destroy');
    Route::get('/halls/{id}/seats', [\App\Http\Controllers\Admin\HallController::class, 'getSeats'])->name('halls.getSeats');
});

/*
|-------------------------------------------------------------------------- 
| Панель пользователя (только для role_id = 2)
|-------------------------------------------------------------------------- 
*/
Route::prefix('user')->name('user.')->middleware(['auth', 'role:2'])->group(function () {
    // Главная страница личного кабинета
    Route::get('/dashboard', [UserUserController::class, 'index'])->name('dashboard');

    // История бронирований
    Route::get('/history', [UserUserController::class, 'history'])->name('history');

    // Обновление профиля и смена пароля
    Route::post('/update', [UserUserController::class, 'updateProfile'])->name('updateProfile');

    // Удаление аккаунта
    Route::delete('/delete', [UserUserController::class, 'deleteAccount'])->name('deleteAccount');

    // Отмена бронирования
    Route::post('/booking/{bookingId}/cancel', [UserUserController::class, 'cancelBooking'])->name('booking.cancel');

    // Генерация PDF билета
    Route::get('/ticket/{bookingId}/pdf', [\App\Http\Controllers\TicketController::class, 'generatePdf'])->name('ticket.pdf');
});

/*
|-------------------------------------------------------------------------- 
| Панель гостя (только для role_id = 3, если понадобится)
|-------------------------------------------------------------------------- 
*/
Route::prefix('guest')->name('guest.')->middleware(['auth', 'role:3'])->group(function () {
    Route::get('/home', function () {
        return view('guest.home');
    })->name('home');
});
