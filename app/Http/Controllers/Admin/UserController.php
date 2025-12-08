<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;

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
            'last_name' => 'required|string|max:255',
            'first_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'login' => 'required|string|max:50|unique:cinema_users,login',
            'password' => 'required|string|min:6|confirmed',
            'role_id' => 'required|exists:roles,id_role',
        ]);

        User::create([
            'last_name' => $validated['last_name'],
            'first_name' => $validated['first_name'],
            'middle_name' => $validated['middle_name'] ?? null,
            'phone' => $validated['phone'] ?? null,
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
        $user->delete();

        return redirect()->route('admin.users.index')->with('success', 'Пользователь удален.');
    }
}
