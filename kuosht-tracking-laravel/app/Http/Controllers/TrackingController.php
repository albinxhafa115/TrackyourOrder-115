<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;

class TrackingController extends Controller
{
    /**
     * Show customer tracking page
     */
    public function show($token)
    {
        $order = Order::where('tracking_token', $token)
            ->with(['courier', 'trackingData'])
            ->firstOrFail();

        // Check if current user is the courier for this order
        $isCourier = auth('courier')->check() &&
                     auth('courier')->user()->id === $order->courier_id;

        return view('tracking.show', compact('order', 'isCourier'));
    }

    /**
     * Get real-time tracking data (AJAX endpoint)
     */
    public function getData($token)
    {
        $order = Order::where('tracking_token', $token)
            ->with('courier')
            ->firstOrFail();

        return response()->json([
            'order' => [
                'order_number' => $order->order_number,
                'status' => $order->status,
                'eta' => $order->eta?->format('H:i'),
                'distance' => $order->distance_to_delivery,
                'delivery_address' => $order->delivery_address,
                'delivery_lat' => $order->delivery_lat,
                'delivery_lng' => $order->delivery_lng,
            ],
            'courier' => [
                'name' => $order->courier->name,
                'phone' => $order->courier->phone,
                'current_lat' => $order->courier->current_lat,
                'current_lng' => $order->courier->current_lng,
                'last_update' => $order->courier->last_location_update?->diffForHumans(),
            ]
        ]);
    }

    /**
     * Update courier position (called by courier app every 5 min)
     */
    public function update(Request $request)
    {
        $request->validate([
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
        ]);

        $courier = auth('courier')->user();

        $courier->update([
            'current_lat' => $request->latitude,
            'current_lng' => $request->longitude,
            'last_location_update' => now(),
        ]);

        // Update all active orders' distance and ETA
        $activeOrders = Order::where('courier_id', $courier->id)
            ->whereIn('status', ['confirmed', 'picked_up', 'in_transit', 'nearby'])
            ->get();

        foreach ($activeOrders as $order) {
            $distance = $this->calculateDistance(
                $courier->current_lat,
                $courier->current_lng,
                $order->delivery_lat,
                $order->delivery_lng
            );

            $order->update([
                'distance_to_delivery' => $distance,
            ]);

            // Check proximity (3-5 minutes = approximately 2-4 km at 40km/h avg speed)
            if ($distance <= 4 && $distance > 0 && $order->status !== 'nearby') {
                $order->update(['status' => 'nearby']);

                return response()->json([
                    'success' => true,
                    'show_call_popup' => true,
                    'order' => [
                        'id' => $order->id,
                        'order_number' => $order->order_number,
                        'customer_name' => $order->customer_name,
                        'customer_phone' => $order->customer_phone,
                        'delivery_address' => $order->delivery_address,
                    ]
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'show_call_popup' => false,
            'position_updated' => true,
        ]);
    }

    /**
     * Calculate distance between two coordinates in kilometers
     */
    private function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371; // km

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }
}
