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
        // Если пользователь не авторизован
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Пожалуйста, войдите в систему.');
        }

        $userRoleId = Auth::user()->role_id;

        // Если роль пользователя не входит в разрешённые
        if (!in_array($userRoleId, $roles)) {
        abort(403, 'У вас недостаточно прав для доступа к этой странице.');
    }

        return $next($request);
    }
}
