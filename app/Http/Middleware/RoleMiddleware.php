<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    /**
     * Обрабатывает входящий запрос.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  mixed  ...$roles  Допустимые role_id (например, 1,2)
     * @return mixed
     */
    public function handle(Request $request, Closure $next, ...$roles)
    {
        // Отладочная информация
        \Log::debug('RoleMiddleware: Checking authentication', [
            'route' => $request->route() ? $request->route()->getName() : 'unknown',
            'url' => $request->url(),
            'session_id' => $request->session()->getId(),
            'auth_check' => Auth::check(),
            'session_data' => $request->session()->all(),
        ]);
        
        // Если пользователь не авторизован
        if (!Auth::check()) {
            \Log::warning('RoleMiddleware: User not authenticated', [
                'route' => $request->route() ? $request->route()->getName() : 'unknown',
                'url' => $request->url(),
                'session_id' => $request->session()->getId(),
                'auth_check' => Auth::check(),
                'session_user_id' => $request->session()->get('login_web_' . sha1('App\Models\User')),
            ]);
            return redirect()->route('login')->with('error', 'Пожалуйста, войдите в систему.');
        }

        $user = Auth::user();
        
        // Проверяем, что пользователь существует
        if (!$user) {
            \Log::error('RoleMiddleware: Auth::user() returned null', [
                'route' => $request->route() ? $request->route()->getName() : 'unknown',
                'url' => $request->url(),
                'session_id' => $request->session()->getId(),
                'auth_check' => Auth::check(),
            ]);
            return redirect()->route('login')->with('error', 'Ошибка авторизации. Пожалуйста, войдите снова.');
        }

        $userRoleId = $user->role_id;
        
        // Преобразуем роли в массив чисел для сравнения
        $allowedRoles = array_map('intval', $roles);

        // Если роль пользователя не входит в разрешённые
        if (!in_array((int)$userRoleId, $allowedRoles, true)) {
            \Log::warning('RoleMiddleware: Access denied', [
                'user_role_id' => $userRoleId,
                'allowed_roles' => $allowedRoles,
                'route' => $request->route() ? $request->route()->getName() : 'unknown',
                'user_id' => $user->id_user,
            ]);
            abort(403, 'У вас недостаточно прав для доступа к этой странице.');
        }

        \Log::debug('RoleMiddleware: Access granted', [
            'user_id' => $user->id_user,
            'role_id' => $userRoleId,
            'route' => $request->route() ? $request->route()->getName() : 'unknown',
        ]);

        return $next($request);
    }
}
