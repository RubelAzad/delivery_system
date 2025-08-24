<?php

namespace App\Http\Controllers;

use App\Models\DeliveryZone;
use App\Models\Order;
use App\Models\DeliveryPerson;
use App\Notifications\DeliveryAssignment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

class DeliveryZoneController extends Controller
{
    public function createZone(Request $request)
    {
        $request->validate([
            'restaurant_id' => 'required|exists:restaurants,id',
            'type' => 'required|in:polygon,radius',
            'coordinates' => 'required_if:type,polygon|array',
            'center' => 'required_if:type,radius|array',
            'radius' => 'required_if:type,radius|numeric',
        ]);

        $zone = DeliveryZone::create([
            'restaurant_id' => $request->restaurant_id,
            'type' => $request->type,
            'coordinates' => $request->type === 'polygon' ? $request->coordinates : null,
            'center' => $request->type === 'radius' ? $request->center : null,
            'radius' => $request->type === 'radius' ? $request->radius : null,
        ]);

        return response()->json(['message' => 'Delivery zone created'], 201);
    }

    public function validateOrder(Request $request)
    {
        $request->validate([
            'restaurant_id' => 'required|exists:restaurants,id',
            'delivery_address' => 'required|array',
        ]);

        $zone = DeliveryZone::where('restaurant_id', $request->restaurant_id)->first();
        if (!$zone) {
            return response()->json(['message' => 'No delivery zone defined'], 400);
        }

        $point = $request->delivery_address;

        $isInside = $zone->type === 'polygon'
            ? $this->pointInPolygon($point, $zone->coordinates)
            : $this->pointInRadius($point, $zone->center, $zone->radius);

        if (!$isInside) {
            return response()->json(['message' => 'Address outside delivery zone'], 400);
        }

        $order = Order::create([
            'restaurant_id' => $request->restaurant_id,
            'delivery_address' => $point,
            'status' => 'pending',
        ]);

        $deliveryPerson = $this->assignDeliveryPerson($point);
        if ($deliveryPerson) {
            $order->update(['delivery_person_id' => $deliveryPerson->id, 'status' => 'assigned']);
            Notification::send($deliveryPerson, new DeliveryAssignment($order));
            return response()->json(['message' => 'Order placed, delivery assigned'], 201);
        }

        return response()->json(['message' => 'No delivery person available'], 400);
    }

    private function pointInPolygon($point, $polygon)
    {
        $x = $point['lat'];
        $y = $point['lng'];
        $inside = false;

        for ($i = 0, $j = count($polygon) - 1; $i < count($polygon); $j = $i++) {
            $xi = $polygon[$i]['lat'];
            $yi = $polygon[$i]['lng'];
            $xj = $polygon[$j]['lat'];
            $yj = $polygon[$j]['lng'];

            if ((($yi > $y) != ($yj > $y)) &&
                ($x < ($xj - $xi) * ($y - $yi) / ($yj - $yi) + $xi)) {
                $inside = !$inside;
            }
        }

        return $inside;
    }

    private function pointInRadius($point, $center, $radius)
    {
        $distance = $this->haversine($point['lat'], $point['lng'], $center['lat'], $center['lng']);
        return $distance <= $radius;
    }

    private function haversine($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371; // km
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon / 2) * sin($dLon / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    private function assignDeliveryPerson($point)
    {
        return DeliveryPerson::select('delivery_peoples.*')
            ->selectRaw(
                '(6371 * acos(cos(radians(?)) * cos(radians(latitude)) * cos(radians(longitude) - radians(?)) + sin(radians(?)) * sin(radians(latitude)))) AS distance',
                [$point['lat'], $point['lng'], $point['lat']]
            )
            ->where('status', 'available')
            ->having('distance', '<', 5)
            ->orderBy('distance')
            ->first();
    }
}