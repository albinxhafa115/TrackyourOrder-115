<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\DeliveryEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $courier = Auth::guard('courier')->user();

        $orders = Order::where('courier_id', $courier->id)
            ->whereDate('scheduled_date', today())
            ->with('customer')
            ->get();

        return view('orders.index', compact('orders'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $order = Order::with(['customer', 'courier', 'deliveryEvents'])->findOrFail($id);
        return view('orders.show', compact('order'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Update order status
     */
    public function updateStatus(Request $request, Order $order)
    {
        $request->validate([
            'status' => 'required|in:confirmed,picked_up,in_transit,nearby,delivered,refused,postponed',
            'delivery_notes' => 'nullable|string',
            'postponed_date' => 'nullable|date|after:today',
        ]);

        $courier = Auth::guard('courier')->user();

        // Verify courier owns this order
        if ($order->courier_id !== $courier->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $oldStatus = $order->status;
        $order->status = $request->status;

        if ($request->status === 'delivered') {
            $order->delivered_at = now();
            $order->delivery_notes = $request->delivery_notes;
        } elseif ($request->status === 'picked_up' && !$order->picked_up_at) {
            $order->picked_up_at = now();
        }

        $order->save();

        // Log delivery event
        DeliveryEvent::create([
            'order_id' => $order->id,
            'event_type' => $request->status,
            'old_status' => $oldStatus,
            'new_status' => $request->status,
            'courier_id' => $courier->id,
            'latitude' => $courier->current_lat,
            'longitude' => $courier->current_lng,
            'notes' => $request->delivery_notes,
        ]);

        // Handle postponed orders
        if ($request->status === 'postponed' && $request->postponed_date) {
            $order->reschedules()->create([
                'original_date' => $order->scheduled_date,
                'new_date' => $request->postponed_date,
                'reason' => $request->delivery_notes ?? 'Customer requested postponement',
                'requested_by' => 'courier',
            ]);

            $order->scheduled_date = $request->postponed_date;
            $order->status = 'confirmed';
            $order->save();
        }

        return response()->json([
            'success' => true,
            'message' => 'Order status updated successfully',
            'order' => $order->fresh(),
        ]);
    }

    /**
     * Send tracking email to customer
     */
    public function sendEmail(Request $request, Order $order)
    {
        $courier = Auth::guard('courier')->user();

        // Verify courier owns this order
        if ($order->courier_id !== $courier->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Check if customer has email
        if (!$order->customer_email) {
            return response()->json([
                'success' => false,
                'message' => 'Klienti nuk ka email adresë të regjistruar.'
            ], 400);
        }

        try {
            $order->sendTrackingEmail();

            return response()->json([
                'success' => true,
                'message' => '✅ Email-i u dërgua me sukses te klienti!',
                'email' => $order->customer_email,
                'tracking_url' => $order->trackingUrl,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gabim gjatë dërgimit të email-it: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
