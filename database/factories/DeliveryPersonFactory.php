<?php

namespace Database\Factories;

use App\Models\DeliveryPerson;
use Illuminate\Database\Eloquent\Factories\Factory;

class DeliveryPersonFactory extends Factory
{
    protected $model = DeliveryPerson::class;

    public function definition()
    {
        return [
            'name' => $this->faker->name,
            'latitude' => $this->faker->latitude,
            'longitude' => $this->faker->longitude,
            'status' => $this->faker->randomElement(['available', 'busy']),
        ];
    }
}