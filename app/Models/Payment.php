<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $table = 'payments';
    protected $primaryKey = 'id_payment';
    public $timestamps = true;

    protected $fillable = [
        'payment_id',
        'payment_status',
        'amount',
        'booking_id',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    /**
     * Связь с бронированием
     */
    public function booking()
    {
        return $this->belongsTo(Booking::class, 'booking_id', 'id_booking');
    }
}
