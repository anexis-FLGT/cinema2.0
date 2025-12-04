<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    /**
     * Показ формы авторизации
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * Обработка авторизации
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'login' => 'required|string',
            'password' => 'required|string',
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            // Проверяем роль пользователя
            $user = Auth::user();

            switch ($user->role_id) {
                case 1:
                    return redirect()->route('admin.dashboard')
                        ->with('success', 'Добро пожаловать, администратор!');
                case 2:
                    return redirect()->route('user.dashboard')
                        ->with('success', 'Добро пожаловать!');
                default:
                    return redirect()->route('guest.home')
                        ->with('success', 'Вы вошли как гость.');
            }
        }

        return back()->withErrors([
            'login' => 'Неверный логин или пароль.',
        ])->onlyInput('login');
    }

    /**
     * Выход из системы
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/')->with('success', 'Вы вышли из системы.');
    }
}