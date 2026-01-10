<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reservation extends Model
{
    use HasFactory;
    
    protected $table = 'reservas';
    
    protected $fillable = [
        'user_id', 'trip_id', 'ticket_code', 'status', 'passenger_name', 'booking_reference', 'price', 'purchase_date'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'purchase_date' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($reservation) {
            if (empty($reservation->booking_reference)) {
                $reservation->booking_reference = 'SHQ-' . strtoupper(uniqid());
            }
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function latestPayment()
    {
        return $this->hasOne(Payment::class)->latestOfMany();
    }
}
