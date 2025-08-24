<?php

namespace Tests\Feature;

use App\Models\DeliveryPerson;
use App\Models\DeliveryZone;
use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;
use App\Notifications\DeliveryAssignment;

class DeliveryZoneTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_delivery_zone(): void
    {
        $restaurant = Restaurant::factory()->create();
        Sanctum::actingAs(User::factory()->create());

        $response = $this->postJson(route('api.delivery-zones.store'), [
            'restaurant_id' => $restaurant->id,
            'type' => 'radius',
            'center' => ['lat' => 40.7128, 'lng' => -74.0060],
            'radius' => 5,
        ]);

        $response->assertStatus(201)->assertJson(['message' => 'Delivery zone created']);
        $this->assertDatabaseHas('delivery_zones', ['restaurant_id' => $restaurant->id, 'type' => 'radius']);
    }

    public function test_order_validation_within_zone(): void
    {
        $restaurant = Restaurant::factory()->create();
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        DeliveryZone::create([
            'restaurant_id' => $restaurant->id,
            'type' => 'radius',
            'coordinates' => null,
            'center' => ['lat' => 40.7128, 'lng' => -74.0060],
            'radius' => 5,
        ]);

        // Ensure at least one available courier is within 5km
        DeliveryPerson::factory()->create([
            'latitude' => 40.7129,
            'longitude' => -74.0061,
            'status' => 'available',
        ]);

        $response = $this->postJson(route('api.orders.validate'), [
            'restaurant_id' => $restaurant->id,
            'delivery_address' => ['lat' => 40.7129, 'lng' => -74.0061],
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('orders', ['restaurant_id' => $restaurant->id]);
    }


    public function test_order_validation_outside_zone(): void
    {
        $restaurant = Restaurant::factory()->create();
        Sanctum::actingAs(User::factory()->create());

        DeliveryZone::create([
            'restaurant_id' => $restaurant->id,
            'type' => 'radius',
            'coordinates' => null,
            'center' => ['lat' => 40.7128, 'lng' => -74.0060],
            'radius' => 5,
        ]);

        $response = $this->postJson(route('api.orders.validate'), [
            'restaurant_id' => $restaurant->id,
            'delivery_address' => ['lat' => 41.7128, 'lng' => -74.0060],
        ]);

        $response->assertStatus(400)->assertJson(['message' => 'Address outside delivery zone']);
    }

    public function test_delivery_person_assignment(): void
    {
        $restaurant = Restaurant::factory()->create();
        Sanctum::actingAs(User::factory()->create());

        DeliveryZone::create([
            'restaurant_id' => $restaurant->id,
            'type' => 'radius',
            'coordinates' => null,
            'center' => ['lat' => 40.7128, 'lng' => -74.0060],
            'radius' => 5,
        ]);

        $deliveryPerson = DeliveryPerson::factory()->create([
            'latitude' => 40.7129,
            'longitude' => -74.0061,
            'status' => 'available',
        ]);

        $response = $this->postJson(route('api.orders.validate'), [
            'restaurant_id' => $restaurant->id,
            'delivery_address' => ['lat' => 40.7129, 'lng' => -74.0061],
        ]);

        $response->assertStatus(201)->assertJson(['message' => 'Order placed, delivery assigned']);
        $this->assertDatabaseHas('orders', ['delivery_person_id' => $deliveryPerson->id, 'status' => 'assigned']);
    }
}
