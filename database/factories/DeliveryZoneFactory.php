<?php

namespace Database\Factories;

use App\Models\DeliveryZone;
use Illuminate\Database\Eloquent\Factories\Factory;

class DeliveryZoneFactory extends Factory
{
    protected $model = DeliveryZone::class;

    public function definition()
    {
        return [
            'restaurant_id' => \App\Models\Restaurant::factory(),
            'type' => 'radius',
            'coordinates' => null,
            'center' => ['lat' => 40.7128, 'lng' => -74.0060], // <-- array, not json_encode
            'radius' => 5,
        ];
    }
}
