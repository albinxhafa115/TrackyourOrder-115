<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$courier = App\Models\Courier::first();

if ($courier) {
    echo "📍 Courier Location Check:\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "👤 Name: {$courier->name}\n";
    echo "📧 Email: {$courier->email}\n";
    echo "🗺️  Current Lat: " . ($courier->current_lat ?? 'NULL') . "\n";
    echo "🗺️  Current Lng: " . ($courier->current_lng ?? 'NULL') . "\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

    if (!$courier->current_lat || !$courier->current_lng) {
        echo "\n⚠️  PROBLEM: Courier has no GPS coordinates!\n";
        echo "This is why directions are not showing.\n";
        echo "\nSetting default location to Prishtina center...\n";

        $courier->update([
            'current_lat' => 42.6629,
            'current_lng' => 21.1655
        ]);

        echo "✅ Updated courier location to: 42.6629, 21.1655\n";
    } else {
        echo "\n✅ Courier has GPS coordinates set!\n";
    }
} else {
    echo "❌ No courier found!\n";
}
