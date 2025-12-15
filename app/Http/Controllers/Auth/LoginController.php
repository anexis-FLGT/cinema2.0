<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

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

        // Используем поле 'login' для авторизации
        $user = User::where('login', $credentials['login'])->first();
        
        if ($user && Hash::check($credentials['password'], $user->password)) {
            // Логиним пользователя
            Auth::login($user, false);

            // Проверяем роль пользователя после авторизации
            $authenticatedUser = Auth::user();
            
            // Проверяем, что пользователь действительно авторизован
            if (!$authenticatedUser) {
                \Log::error('Auth failed after login', [
                    'user_id' => $user->id_user,
                    'login' => $user->login,
                    'role_id' => $user->role_id,
                    'session_id' => $request->session()->getId(),
                ]);
                return back()->withErrors([
                    'login' => 'Ошибка авторизации. Попробуйте еще раз.',
                ])->onlyInput('login');
            }

            \Log::info('User logged in successfully', [
                'user_id' => $authenticatedUser->id_user,
                'login' => $authenticatedUser->login,
                'role_id' => $authenticatedUser->role_id,
                'session_id' => $request->session()->getId(),
            ]);

            // Редирект в зависимости от роли
            if ($authenticatedUser->role_id == 1) {
                return redirect()->route('admin.dashboard')
                    ->with('success', 'Добро пожаловать, администратор!');
            } elseif ($authenticatedUser->role_id == 2) {
                return redirect()->route('home')
                    ->with('success', 'Добро пожаловать!');
            } else {
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