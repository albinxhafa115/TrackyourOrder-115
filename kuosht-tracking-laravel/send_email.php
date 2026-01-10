<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$order = App\Models\Order::find(1);

if (!$order) {
    echo "Order not found!\n";
    exit(1);
}

if (!$order->customer_email) {
    echo "Order does not have customer email!\n";
    exit(1);
}

try {
    $order->sendTrackingEmail();
    echo "✅ Email sent successfully!\n";
    echo "📧 To: " . $order->customer_email . "\n";
    echo "📦 Order: " . $order->order_number . "\n";
    echo "🔗 Tracking URL: " . $order->trackingUrl . "\n";
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
