<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        \App\Models\Restaurant::factory(10)->create();
        \App\Models\DeliveryPerson::factory(5)->create();
        \App\Models\DeliveryZone::factory(5)->create();
        \App\Models\Order::factory(5)->create();
        \App\Models\User::factory(1)->create();
    }
}