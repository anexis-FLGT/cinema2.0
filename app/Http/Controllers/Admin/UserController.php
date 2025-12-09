<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Role;
use App\Models\Booking;
use App\Models\Payment;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    // Список пользователей с пагинацией
    public function index()
    {
        $users = User::with('role')->paginate(10);
        // Исключаем роль "Гость" из списка для редактирования
        $roles = Role::where('role_name', '!=', 'Гость')->get();

        return view('admin.users', compact('users', 'roles'));
    }

    // Добавление нового пользователя
    public function store(Request $request)
    {
        $validated = $request->validate([
            'last_name' => ['required', 'string', 'max:255', 'regex:/^[a-zA-Zа-яА-ЯёЁ\s]+$/u'],
            'first_name' => ['required', 'string', 'max:255', 'regex:/^[a-zA-Zа-яА-ЯёЁ\s]+$/u'],
            'middle_name' => ['nullable', 'string', 'max:255', 'regex:/^[a-zA-Zа-яА-ЯёЁ\s]*$/u'],
            'phone' => 'required|string|max:18',
            'login' => 'required|string|max:50|unique:cinema_users,login',
            'password' => [
                'required',
                'confirmed',
                'min:8',
                'regex:/[A-ZА-Я]/', // хотя бы одна заглавная
                'regex:/[a-zа-я]/', // хотя бы одна строчная
                'regex:/[0-9]/', // хотя бы одна цифра
                'regex:/[@$!%*?&.,?":{}|<>]/' // хотя бы один символ
            ],
            'role_id' => 'required|exists:roles,id_role',
        ], 
        [
            'last_name.regex' => 'Фамилия должна содержать только буквы (русские или английские).',
            'first_name.regex' => 'Имя должно содержать только буквы (русские или английские).',
            'middle_name.regex' => 'Отчество должно содержать только буквы (русские или английские).',
            'phone.required' => 'Поле "Телефон" обязательно для заполнения.',
            'password.regex' => 'Пароль должен содержать заглавные и строчные буквы, цифры и символ.',
            'password.confirmed' => 'Пароли не совпадают.',
        ]);

        User::create([
            'last_name' => $validated['last_name'],
            'first_name' => $validated['first_name'],
            'middle_name' => $validated['middle_name'] ?? null,
            'phone' => $validated['phone'],
            'login' => $validated['login'],
            'password' => Hash::make($validated['password']),
            'role_id' => $validated['role_id'],
        ]);

        return redirect()->route('admin.users.index')->with('success', 'Пользователь добавлен.');
    }

    // Обновление роли пользователя
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        // Проверка: активный администратор не может изменить свою роль
        if (auth()->user()->id_user == $user->id_user && $user->role_id == 1) {
            return redirect()->route('admin.users.index')
                ->with('error', 'Нельзя изменить роль активного администратора.');
        }

        $validated = $request->validate([
            'role_id' => 'required|exists:roles,id_role',
        ]);

        $user->role_id = $validated['role_id'];
        $user->save();

        return redirect()->route('admin.users.index')->with('success', 'Роль пользователя обновлена.');
    }

    // Удаление пользователя
    public function destroy($id)
    {
        $user = User::findOrFail($id);

        // Проверяем наличие активных (не отмененных) бронирований у пользователя
        $activeBookingsCount = Booking::where('user_id', $user->id_user)
            ->where(function($query) {
                $query->whereHas('payment', function($q) {
                    $q->where('payment_status', '!=', 'отменено');
                })
                ->orWhereDoesntHave('payment');
            })
            ->count();

        if ($activeBookingsCount > 0) {
            return redirect()->route('admin.users.index')
                ->with('error', "Невозможно удалить пользователя! У пользователя есть {$activeBookingsCount} " . 
                    ($activeBookingsCount == 1 ? 'активное бронирование' : ($activeBookingsCount < 5 ? 'активных бронирования' : 'активных бронирований')) . '.');
        }

        // Если активных бронирований нет, удаляем все бронирования пользователя и их платежи, затем пользователя
        DB::transaction(function () use ($user) {
            // Получаем все бронирования пользователя (включая отмененные)
            $allBookingIds = Booking::where('user_id', $user->id_user)->pluck('id_booking');

            // Удаляем все платежи, связанные с этими бронированиями
            if ($allBookingIds->isNotEmpty()) {
                Payment::whereIn('booking_id', $allBookingIds)->delete();
            }

            // Удаляем все бронирования пользователя
            Booking::where('user_id', $user->id_user)->delete();

            // Удаляем пользователя
            $user->delete();
        });

        return redirect()->route('admin.users.index')->with('success', 'Пользователь удален.');
    }
}
