<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Hall extends Model
{
    protected $table = 'halls';
    protected $primaryKey = 'id_hall';
    public $timestamps = false;

    protected $fillable = [
        'hall_name',
        'quantity_seats',
        'type_hall',
        'description_hall',
        'hall_photo',
    ];

    public function seats()
    {
        return $this->hasMany(Seat::class, 'hall_id', 'id_hall');
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class, 'hall_id', 'id_hall');
    }

    public function sessions()
    {
        return $this->hasMany(Session::class, 'hall_id', 'id_hall');
    }
}


