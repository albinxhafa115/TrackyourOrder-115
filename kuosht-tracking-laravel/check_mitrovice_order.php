<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$order = App\Models\Order::where('customer_email', 'albinxhafa6@gmail.com')->first();

if (!$order) {
    echo "❌ Order not found!\n";
    exit(1);
}

echo "✅ Order Details for Mitrovicë:\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "📦 Order Number: {$order->order_number}\n";
echo "👤 Customer: {$order->customer_name}\n";
echo "📧 Email: {$order->customer_email}\n";
echo "📍 Address: {$order->delivery_address}\n";
echo "🗺️  Coordinates: {$order->delivery_lat}, {$order->delivery_lng}\n";
echo "🔗 Google Maps: https://www.google.com/maps/dir/?api=1&destination={$order->delivery_lat},{$order->delivery_lng}\n";
echo "🌐 Tracking URL: " . route('tracking.show', ['token' => $order->tracking_token]) . "\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
