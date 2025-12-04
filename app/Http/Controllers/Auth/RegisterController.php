<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class RegisterController extends Controller
{
    /**
     * Показать форму регистрации
     */
    public function showRegistrationForm()
    {
        return view('auth.register');
    }

    /**
     * Обработка формы регистрации
     */
    public function register(Request $request)
    {
        $validated = $request->validate([
            'last_name' => 'required|string|max:45',
            'first_name' => 'required|string|max:45',
            'middle_name' => 'nullable|string|max:45',
            'phone' => 'required|string|max:18',
            'login' => 'required|string|max:45|unique:cinema_users,login',
            'password' => [
                'required',
                'confirmed',
                'min:8',
                'regex:/[A-Z]/', // хотя бы одна заглавная
                'regex:/[a-z]/', // хотя бы одна строчная
                'regex:/[0-9]/', // хотя бы одна цифра
                'regex:/[@$!%*?&.]/' // хотя бы один символ
            ],
        ], 
        [
            'password.regex' => 'Пароль должен содержать заглавные и строчные буквы, цифры и символ.',
            'password.confirmed' => 'Пароли не совпадают.',
        ]);

        // Создаём пользователя с ролью "гость" (2)
        User::create([
            'last_name' => $validated['last_name'],
            'first_name' => $validated['first_name'],
            'middle_name' => $validated['middle_name'] ?? null,
            'phone' => $validated['phone'],
            'login' => $validated['login'],
            'password' => Hash::make($validated['password']),
            'role_id' => 2,
        ]);

        return redirect('/login')->with('success', 'Регистрация успешна! Теперь войдите в систему.');
    }
}
