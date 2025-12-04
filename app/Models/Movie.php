<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Movie extends Model
{
    protected $table = 'movies';
    protected $primaryKey = 'id_movie';
    public $timestamps = false;

    protected $fillable = [
        'movie_title',
        'poster',
        'baner',
        'duration',
        'age_limit',
        'description',
        'producer',
    ];

    public function genres()
    {
        return $this->belongsToMany(Genre::class, 'genre_movie', 'movie_id', 'genre_id');
    }

    public function sessions()
    {
        return $this->hasMany(Session::class, 'movie_id', 'id_movie');
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class, 'movie_id', 'id_movie');
    }
}
