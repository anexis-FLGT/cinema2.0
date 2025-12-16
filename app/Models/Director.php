<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Director extends Model
{
    protected $table = 'directors';
    protected $primaryKey = 'id_director';
    public $timestamps = false;

    protected $fillable = [
        'name',
    ];

    public function movies()
    {
        return $this->belongsToMany(Movie::class, 'director_movie', 'director_id', 'movie_id');
    }
}


