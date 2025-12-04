<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Seat extends Model
{
    protected $table = 'seats';
    protected $primaryKey = 'id_seat';
    public $timestamps = false;

    protected $fillable = [
        'row_number',
        'seat_number',
        'status',
        'hall_id',
    ];

    public function hall()
    {
        return $this->belongsTo(Hall::class, 'hall_id', 'id_hall');
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class, 'seat_id', 'id_seat');
    }
}


