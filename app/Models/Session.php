<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Session extends Model
{
    protected $table = 'cinema_sessions';
    protected $primaryKey = 'id_session';
    public $timestamps = false;

    protected $fillable = [
        'date_time_session',
        'movie_id',
        'hall_id',
        'is_archived',
    ];

    protected $casts = [
        'date_time_session' => 'datetime',
        'is_archived' => 'boolean',
    ];

    public function movie()
    {
        return $this->belongsTo(Movie::class, 'movie_id', 'id_movie');
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class, 'session_id', 'id_session');
    }

    public function hall()
    {
        return $this->belongsTo(Hall::class, 'hall_id', 'id_hall');
    }
}


