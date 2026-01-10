<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$order = App\Models\Order::first();
$order->update([
    'customer_email' => 'albinxhafa6@gmail.com',
    'customer_name' => 'Albin Xhafa',
    'delivery_address' => 'Mitrovicë, Kosovë'
]);

echo "Order updated successfully!\n";
echo "Email: " . $order->customer_email . "\n";
echo "Name: " . $order->customer_name . "\n";
echo "Address: " . $order->delivery_address . "\n";
