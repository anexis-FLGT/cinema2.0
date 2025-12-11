<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Booking;
use App\Models\Seat;
use App\Models\Payment;

class UserController extends Controller
{
    /**
     * Отображение личного кабинета пользователя
     */
    public function index(Request $request)
    {
        // Автоматически освобождаем истекшие бронирования
        Booking::expireOldBookings();
        
        $user = Auth::user(); // Получаем текущего пользователя
        $now = \Carbon\Carbon::now();
        
        // Получаем активные бронирования (будущие сеансы)
        // expireOldBookings уже удалил истекшие бронирования, поэтому просто фильтруем по статусу
        $activeBookingsQuery = Booking::with(['session.movie', 'session.hall', 'session', 'seat', 'payment'])
            ->join('cinema_sessions', 'bookings.session_id', '=', 'cinema_sessions.id_session')
            ->where('bookings.user_id', $user->id_user)
            ->where(function($query) {
                // Показываем только не отмененные бронирования
                $query->whereHas('payment', function($q) {
                    $q->where('payment_status', '!=', 'отменено');
                })->orWhereDoesntHave('payment');
            })
            ->where('cinema_sessions.date_time_session', '>', $now)
            ->orderBy('cinema_sessions.date_time_session', 'desc')
            ->select('bookings.*');
        
        // Пагинация для активных бронирований
        $activeBookings = $activeBookingsQuery->paginate(6, ['*'], 'active_page');
        
        // Группировка активных бронирований по дате и времени сеанса
        $activeBookingsGrouped = $activeBookings->getCollection()->groupBy(function($booking) {
            if ($booking->session && $booking->session->date_time_session) {
                return \Carbon\Carbon::parse($booking->session->date_time_session)->format('Y-m-d H:i');
            }
            return 'unknown';
        })->sortKeysDesc();
        
        return view('user.dashboard', compact('user', 'activeBookings', 'activeBookingsGrouped'));
    }

    /**
     * Отображение истории бронирований
     */
    public function history(Request $request)
    {
        // Автоматически освобождаем истекшие бронирования
        Booking::expireOldBookings();
        
        $user = Auth::user();
        $now = \Carbon\Carbon::now();
        
        // Получаем историю бронирований (прошедшие сеансы)
        $historyBookingsQuery = Booking::with(['session.movie', 'session.hall', 'session', 'seat', 'payment'])
            ->join('cinema_sessions', 'bookings.session_id', '=', 'cinema_sessions.id_session')
            ->where('bookings.user_id', $user->id_user)
            ->where(function($query) {
                $query->whereHas('payment', function($q) {
                    $q->where('payment_status', '!=', 'отменено');
                })->orWhereDoesntHave('payment');
            })
            ->where('cinema_sessions.date_time_session', '<=', $now)
            ->orderBy('cinema_sessions.date_time_session', 'desc')
            ->select('bookings.*');
        
        // Пагинация для истории
        $historyBookings = $historyBookingsQuery->paginate(10);
        
        // Группировка истории по дате и времени сеанса
        $historyBookingsGrouped = $historyBookings->getCollection()->groupBy(function($booking) {
            if ($booking->session && $booking->session->date_time_session) {
                return \Carbon\Carbon::parse($booking->session->date_time_session)->format('Y-m-d H:i');
            }
            return 'unknown';
        })->sortKeysDesc();
        
        return view('user.history', compact('user', 'historyBookings', 'historyBookingsGrouped'));
    }

    /**
     * Обновление профиля и пароля пользователя
     */
    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        // Валидация
        $validated = $request->validate([
            'last_name' => 'required|string|max:255',
            'first_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'login' => 'required|string|max:50|unique:cinema_users,login,' . $user->id_user . ',id_user',
            'password' => [
                'nullable',
                'confirmed',
                'min:8',
                'regex:/[A-ZА-Я]/', // хотя бы одна заглавная
                'regex:/[a-zа-я]/', // хотя бы одна строчная
                'regex:/[0-9]/', // хотя бы одна цифра
                'regex:/[@$!%*?&.,?":{}|<>]/' // хотя бы один символ
            ],
        ], [
            'password.regex' => 'Пароль должен содержать заглавные и строчные буквы, цифры и символ.',
            'password.confirmed' => 'Пароли не совпадают.',
        ]);

        // Обновляем данные
        $user->last_name = $validated['last_name'];
        $user->first_name = $validated['first_name'];
        $user->middle_name = $validated['middle_name'] ?? null;
        $user->phone = $validated['phone'] ?? null;
        $user->login = $validated['login'];

        // Если пользователь вводит новый пароль
        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        return redirect()->route('user.dashboard')->with('success', 'Профиль успешно обновлён.');
    }

    /**
     * Удаление аккаунта пользователя
     */
    public function deleteAccount(Request $request)
    {
        $user = Auth::user();
        $now = \Carbon\Carbon::now();

        // Проверяем наличие активных бронирований
        $activeBookingsCount = Booking::with('payment')
            ->join('cinema_sessions', 'bookings.session_id', '=', 'cinema_sessions.id_session')
            ->where('bookings.user_id', $user->id_user)
            ->whereHas('payment', function($query) {
                $query->where('payment_status', '!=', 'отменено');
            })
            ->where('cinema_sessions.date_time_session', '>', $now)
            ->count();

        // Если есть активные бронирования и пользователь не подтвердил удаление с предупреждением
        if ($activeBookingsCount > 0 && $request->input('confirm_with_bookings') != '1') {
            return redirect()->route('user.dashboard')
                ->with('error', 'У вас есть активные бронирования. Если вы действительно хотите удалить аккаунт, подтвердите удаление ещё раз.');
        }

        // Используем транзакцию для безопасного удаления всех связанных данных
        DB::transaction(function () use ($user) {
            // Получаем ID всех бронирований пользователя
            $bookingIds = Booking::where('user_id', $user->id_user)->pluck('id_booking');
            
            // Удаляем все платежи, связанные с бронированиями пользователя
            if ($bookingIds->isNotEmpty()) {
                Payment::whereIn('booking_id', $bookingIds)->delete();
            }
            
            // Удаляем все бронирования пользователя
            Booking::where('user_id', $user->id_user)->delete();
            
            // Удаляем самого пользователя
            $user->delete();
        });

        // Выходим из системы после успешного удаления
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/')->with('success', 'Ваш аккаунт успешно удалён.');
    }

    /**
     * Отмена бронирования
     */
    public function cancelBooking(Request $request, $bookingId)
    {
        $user = Auth::user();
        
        // Находим бронирование
        $booking = Booking::with('seat')->findOrFail($bookingId);
        
        // Проверяем, что бронирование принадлежит текущему пользователю
        if ($booking->user_id != $user->id_user) {
            return redirect()->route('user.dashboard')
                ->with('error', 'У вас нет прав для отмены этого бронирования');
        }
        
        // Проверяем, что бронирование не отменено
        $payment = $booking->payment;
        if (!$payment) {
            return redirect()->route('user.dashboard')
                ->with('error', 'Платеж для бронирования не найден');
        }
        
        if ($payment->payment_status === 'отменено') {
            return redirect()->route('user.dashboard')
                ->with('error', 'Бронирование уже отменено');
        }
        
        // Обновляем статус платежа
        $payment->payment_status = 'отменено';
        $payment->save();
        
        // НЕ меняем статус места - забронированность определяется только по bookings для конкретного session_id
        // Место может быть забронировано на другие сеансы, поэтому не нужно менять его статус
        
        return redirect()->route('user.dashboard')
            ->with('success', 'Бронирование успешно отменено. Место освобождено.');
    }
}
