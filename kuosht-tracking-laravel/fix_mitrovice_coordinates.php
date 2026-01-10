<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Update the old Mitrovicë order with correct coordinates
$order = App\Models\Order::where('id', 3)->first();

if ($order) {
    $order->update([
        'delivery_lat' => 42.8914,
        'delivery_lng' => 20.8664,
        'delivery_address' => 'Rruga Mbretëresha Teutë, Mitrovicë 40000'
    ]);

    echo "✅ Updated Order #3 with correct Mitrovicë coordinates:\n";
    echo "   Lat: 42.8914, Lng: 20.8664\n";
    echo "   Address: Rruga Mbretëresha Teutë, Mitrovicë 40000\n";
    echo "   🔗 Google Maps: https://www.google.com/maps/dir/?api=1&destination=42.8914,20.8664\n";
} else {
    echo "❌ Order #3 not found!\n";
}

// Also update order #1 if it exists
$order1 = App\Models\Order::where('id', 1)->first();
if ($order1 && $order1->delivery_address == 'Mitrovicë, Kosovë') {
    $order1->update([
        'delivery_lat' => 42.8914,
        'delivery_lng' => 20.8664,
        'delivery_address' => 'Rruga Mbretëresha Teutë, Mitrovicë 40000'
    ]);
    echo "\n✅ Updated Order #1 with correct Mitrovicë coordinates\n";
}
