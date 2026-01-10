<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CourierDashboardController extends Controller
{
    /**
     * Show courier dashboard
     */
    public function index()
    {
        $courier = Auth::guard('courier')->user();

        // Get today's orders for this courier
        $todayOrders = Order::where('courier_id', $courier->id)
            ->whereDate('scheduled_date', today())
            ->with('customer')
            ->get();

        // Sort by distance from courier's current location if available
        if ($courier->current_lat && $courier->current_lng) {
            $todayOrders = $todayOrders->sortBy(function ($order) use ($courier) {
                return $this->calculateDistance(
                    $courier->current_lat,
                    $courier->current_lng,
                    $order->delivery_lat,
                    $order->delivery_lng
                );
            });
        } else {
            $todayOrders = $todayOrders->sortBy('status');
        }

        // Get all active orders
        $activeOrders = Order::where('courier_id', $courier->id)
            ->whereIn('status', ['confirmed', 'picked_up', 'in_transit', 'nearby'])
            ->with('customer')
            ->get();

        // Statistics
        $stats = [
            'total_today' => $todayOrders->count(),
            'delivered' => $todayOrders->where('status', 'delivered')->count(),
            'in_transit' => $todayOrders->whereIn('status', ['picked_up', 'in_transit', 'nearby'])->count(),
            'pending' => $todayOrders->where('status', 'confirmed')->count(),
        ];

        return view('courier.dashboard', compact('courier', 'todayOrders', 'activeOrders', 'stats'));
    }

    /**
     * Calculate distance between two coordinates in kilometers using Haversine formula
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

    /**
     * Get courier's current orders (for AJAX)
     */
    public function getOrders()
    {
        $courier = Auth::guard('courier')->user();

        $orders = Order::where('courier_id', $courier->id)
            ->whereDate('scheduled_date', today())
            ->with('customer')
            ->get();

        return response()->json($orders);
    }
}
