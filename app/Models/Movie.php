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
        'release_year',
        'age_limit',
        'description',
        'director',
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
        return $this->hasManyThrough(
            Booking::class,
            Session::class,
            'movie_id',   // Foreign key on sessions table
            'session_id', // Foreign key on bookings table
            'id_movie',   // Local key on movies table
            'id_session'  // Local key on sessions table
        );
    }
}
