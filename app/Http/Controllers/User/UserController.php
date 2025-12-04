<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Booking;
use App\Models\Seat;

class UserController extends Controller
{
    /**
     * Отображение личного кабинета пользователя
     */
    public function index()
    {
        $user = Auth::user(); // Получаем текущего пользователя
        
        // Получаем все бронирования пользователя с связанными данными
        $bookings = Booking::with(['movie', 'session', 'hall', 'seat', 'payment'])
            ->where('user_id', $user->id_user)
            ->whereHas('payment', function($query) {
                $query->where('payment_status', '!=', 'отменено');
            })
            ->orderBy('show_date', 'desc')
            ->orderBy('show_time', 'desc')
            ->get();
        
        return view('user.dashboard', compact('user', 'bookings'));
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
            'password' => 'nullable|string|min:6|confirmed',
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

        Auth::logout();
        $user->delete();

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
        
        // Обновляем статус места на "свободно"
        if ($booking->seat) {
            $seat = Seat::find($booking->seat_id);
            if ($seat) {
                $seat->status = 'Свободно';
                $seat->save();
            }
        }
        
        return redirect()->route('user.dashboard')
            ->with('success', 'Бронирование успешно отменено. Место освобождено.');
    }
}
