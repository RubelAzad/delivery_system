<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DeliveryPerson extends Model
{
    use HasFactory, Notifiable;

    protected $table = 'delivery_peoples';
    protected $fillable = ['name', 'latitude', 'longitude', 'status'];

    protected $casts = [
        'latitude' => 'float',
        'longitude' => 'float',
    ];
}