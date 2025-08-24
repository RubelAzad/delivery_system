<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DeliveryZone extends Model
{
    use HasFactory, Notifiable;
    
    protected $table = 'delivery_zones';
    protected $fillable = ['restaurant_id', 'type', 'coordinates', 'center', 'radius'];

    protected $casts = [
        'coordinates' => 'array',
        'center' => 'array',
    ];

    public function restaurant()
    {
        return $this->belongsTo(Restaurant::class);
    }
}