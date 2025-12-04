<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    // Указываем, с какой таблицей работает модель
    protected $table = 'cinema_users';

    // Указываем первичный ключ
    protected $primaryKey = 'id_user';

    // Если в таблице нет полей created_at / updated_at, выключаем их
    public $timestamps = false;

    // Разрешённые поля для массового заполнения
    protected $fillable = [
        'last_name',
        'first_name',
        'middle_name',
        'phone',
        'login',
        'password',
        'role_id',
    ];

    // Скрываем пароль при сериализации
    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }

    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id', 'id_role'); //для разраничения по ролям
    }
}
