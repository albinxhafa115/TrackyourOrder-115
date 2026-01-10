<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Get first courier
$courier = App\Models\Courier::first();

// Mitrovicë coordinates: 42.8914, 20.8664
// Prishtinë coordinates: 42.6629, 21.1655
// Pejë coordinates: 42.6589, 20.2889
// Prizren coordinates: 42.2139, 20.7397
// Ferizaj coordinates: 42.3703, 21.1483

$orders = [
    [
        'order_number' => 'KU' . date('Ymd') . '010',
        'customer_name' => 'Albin Xhafa',
        'customer_phone' => '+38349123456',
        'customer_email' => 'albinxhafa6@gmail.com',
        'delivery_address' => 'Rruga Mbretëresha Teutë, Mitrovicë 40000',
        'delivery_lat' => 42.8914,
        'delivery_lng' => 20.8664,
        'order_value' => 125.50,
        'payment_method' => 'cash',
        'priority' => 1,
    ],
    [
        'order_number' => 'KU' . date('Ymd') . '011',
        'customer_name' => 'Dardan Morina',
        'customer_phone' => '+38349234567',
        'customer_email' => 'dardan.m@example.com',
        'delivery_address' => 'Rruga Bill Clinton, Prishtinë 10000',
        'delivery_lat' => 42.6650,
        'delivery_lng' => 21.1620,
        'order_value' => 89.99,
        'payment_method' => 'card',
        'priority' => 0,
    ],
    [
        'order_number' => 'KU' . date('Ymd') . '012',
        'customer_name' => 'Arta Krasniqi',
        'customer_phone' => '+38349345678',
        'customer_email' => 'arta.k@example.com',
        'delivery_address' => 'Rruga Dëshmorët e Kombit, Pejë 30000',
        'delivery_lat' => 42.6589,
        'delivery_lng' => 20.2889,
        'order_value' => 156.00,
        'payment_method' => 'cash',
        'priority' => 0,
    ],
    [
        'order_number' => 'KU' . date('Ymd') . '013',
        'customer_name' => 'Faton Berisha',
        'customer_phone' => '+38349456789',
        'customer_email' => 'faton.b@example.com',
        'delivery_address' => 'Sheshi Shatërvan, Prizren 20000',
        'delivery_lat' => 42.2139,
        'delivery_lng' => 20.7397,
        'order_value' => 234.75,
        'payment_method' => 'card',
        'priority' => 0,
    ],
    [
        'order_number' => 'KU' . date('Ymd') . '014',
        'customer_name' => 'Elona Hoxha',
        'customer_phone' => '+38349567890',
        'customer_email' => 'elona.h@example.com',
        'delivery_address' => 'Rruga Adem Jashari, Ferizaj 70000',
        'delivery_lat' => 42.3703,
        'delivery_lng' => 21.1483,
        'order_value' => 67.50,
        'payment_method' => 'cash',
        'priority' => 0,
    ],
    [
        'order_number' => 'KU' . date('Ymd') . '015',
        'customer_name' => 'Blerta Gashi',
        'customer_phone' => '+38349678901',
        'customer_email' => 'blerta.g@example.com',
        'delivery_address' => 'Rruga Rexhep Luci, Prishtinë 10000',
        'delivery_lat' => 42.6700,
        'delivery_lng' => 21.1580,
        'order_value' => 198.25,
        'payment_method' => 'card',
        'priority' => 0,
    ],
];

echo "Creating orders for today...\n\n";

foreach ($orders as $orderData) {
    $order = App\Models\Order::create([
        'order_number' => $orderData['order_number'],
        'customer_name' => $orderData['customer_name'],
        'customer_phone' => $orderData['customer_phone'],
        'customer_email' => $orderData['customer_email'],
        'delivery_address' => $orderData['delivery_address'],
        'delivery_lat' => $orderData['delivery_lat'],
        'delivery_lng' => $orderData['delivery_lng'],
        'status' => 'confirmed',
        'scheduled_date' => today(),
        'scheduled_time_start' => '09:00',
        'scheduled_time_end' => '18:00',
        'courier_id' => $courier->id,
        'assigned_at' => now(),
        'payment_method' => $orderData['payment_method'],
        'payment_status' => 'pending',
        'order_value' => $orderData['order_value'],
        'priority' => $orderData['priority'],
    ]);

    echo "✅ Created: {$order->order_number} - {$order->customer_name} ({$order->delivery_address})\n";
    echo "   Tracking URL: http://127.0.0.1:8000/track/{$order->tracking_token}\n\n";
}

echo "\n🎉 All orders created successfully!\n";
echo "Total orders: " . count($orders) . "\n";
