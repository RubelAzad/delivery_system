<?php

namespace Database\Factories;

use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition()
    {
        return [
            'restaurant_id' => \App\Models\Restaurant::factory(),
            'delivery_address' => ['lat' => 40.7129, 'lng' => -74.0061], // <-- array
            'status' => 'pending',
            'delivery_person_id' => null,
        ];
    }
}
