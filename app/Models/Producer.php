<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Producer extends Model
{
    protected $table = 'producers';
    protected $primaryKey = 'id_producer';
    public $timestamps = false;

    protected $fillable = [
        'name',
    ];

    public function movies()
    {
        return $this->belongsToMany(Movie::class, 'movie_producer', 'producer_id', 'movie_id');
    }
}


