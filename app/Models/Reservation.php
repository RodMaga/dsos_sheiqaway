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
}
