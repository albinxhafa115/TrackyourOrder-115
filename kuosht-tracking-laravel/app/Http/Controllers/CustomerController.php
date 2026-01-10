<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Customer;
use App\Models\Courier;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CustomerController extends Controller
{
    /**
     * Show the order creation form
     */
    public function create()
    {
        return view('customer.create-order');
    }

    /**
     * Store a new order
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_email' => 'required|email|max:255',
            'customer_phone' => 'required|string|max:20',
            'delivery_address' => 'required|string|max:500',
            'delivery_lat' => 'required|numeric|between:-90,90',
            'delivery_lng' => 'required|numeric|between:-180,180',
            'notes' => 'nullable|string|max:1000',
        ]);

        // Find or create customer
        $customer = Customer::firstOrCreate(
            ['email' => $validated['customer_email']],
            [
                'name' => $validated['customer_name'],
                'phone' => $validated['customer_phone'],
                'address' => $validated['delivery_address'],
            ]
        );

        // Assign to courier with least active orders (smart distribution)
        $courier = Courier::withCount([
            'orders' => function ($query) {
                $query->whereIn('status', ['confirmed', 'picked_up', 'in_transit', 'nearby']);
            }
        ])
        ->orderBy('orders_count', 'asc')
        ->first();

        if (!$courier) {
            return back()->with('error', 'Asnjë kurier nuk është në dispozicion momentalisht.');
        }

        // Create order
        $order = Order::create([
            'order_number' => 'ORD-' . strtoupper(Str::random(8)),
            'customer_id' => $customer->id,
            'customer_name' => $validated['customer_name'],
            'customer_email' => $validated['customer_email'],
            'customer_phone' => $validated['customer_phone'],
            'courier_id' => $courier->id,
            'delivery_address' => $validated['delivery_address'],
            'delivery_lat' => $validated['delivery_lat'],
            'delivery_lng' => $validated['delivery_lng'],
            'scheduled_date' => now()->addDay(), // Tomorrow by default
            'status' => 'confirmed',
            'tracking_token' => Str::random(32),
            'notes' => $validated['notes'],
        ]);

        return redirect()->route('customer.success', ['order' => $order->id])
            ->with('success', 'Porosia u krijua me sukses!');
    }

    /**
     * Show success page
     */
    public function success(Order $order)
    {
        return view('customer.order-success', compact('order'));
    }
}
