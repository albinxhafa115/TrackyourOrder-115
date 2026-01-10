<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$orders = App\Models\Order::where('customer_email', 'albinxhafa6@gmail.com')->get();

echo "📋 All orders for albinxhafa6@gmail.com:\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

foreach ($orders as $order) {
    echo "📦 Order #{$order->id}: {$order->order_number}\n";
    echo "   📍 Address: {$order->delivery_address}\n";
    echo "   🗺️  Coordinates: {$order->delivery_lat}, {$order->delivery_lng}\n";
    echo "   📅 Created: {$order->created_at}\n";
    echo "   🔗 Google Maps: https://www.google.com/maps/dir/?api=1&destination={$order->delivery_lat},{$order->delivery_lng}\n";
    echo "\n";
}

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "Total: " . $orders->count() . " orders\n";
