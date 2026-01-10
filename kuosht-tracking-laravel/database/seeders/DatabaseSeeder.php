<?php

namespace Database\Seeders;

use App\Models\Courier;
use App\Models\Customer;
use App\Models\Order;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create test courier
        $courier = Courier::create([
            'name' => 'Leart Krasniqi',
            'email' => 'leart@kuosht.com',
            'password_hash' => Hash::make('courier123'),
            'phone' => '+38344123456',
            'device_id' => 'courier_1',
            'status' => 'active',
            'current_lat' => 42.6629,
            'current_lng' => 21.1655,
        ]);

        // Create additional couriers
        Courier::create([
            'name' => 'Driton Shala',
            'email' => 'driton@kuosht.com',
            'password_hash' => Hash::make('courier123'),
            'phone' => '+38344987654',
            'device_id' => 'courier_2',
            'status' => 'active',
        ]);

        Courier::create([
            'name' => 'Blerina Gashi',
            'email' => 'blerina@kuosht.com',
            'password_hash' => Hash::make('courier123'),
            'phone' => '+38344876543',
            'device_id' => 'courier_3',
            'status' => 'active',
        ]);

        // Create customers
        $customer1 = Customer::create([
            'name' => 'Agron Mustafa',
            'email' => 'agron@example.com',
            'phone' => '+38344111222',
            'address' => 'Rruga Fehmi Agani 12, Prishtinë',
        ]);

        $customer2 = Customer::create([
            'name' => 'Besarta Krasniqi',
            'email' => 'besarta@example.com',
            'phone' => '+38344222333',
            'address' => 'Lagjia Muhaxherëve 8, Prishtinë',
        ]);

        Customer::create([
            'name' => 'Arben Hoxha',
            'email' => 'arben@example.com',
            'phone' => '+38344333444',
            'address' => 'Rruga Bill Clinton 44, Prishtinë',
        ]);

        // Create orders
        Order::create([
            'order_number' => 'KU20260102001',
            'customer_id' => $customer1->id,
            'customer_name' => $customer1->name,
            'customer_phone' => $customer1->phone,
            'customer_email' => $customer1->email,
            'delivery_address' => $customer1->address,
            'delivery_lat' => 42.6629,
            'delivery_lng' => 21.1655,
            'status' => 'confirmed',
            'scheduled_date' => today(),
            'courier_id' => $courier->id,
            'payment_method' => 'cash',
            'payment_status' => 'pending',
            'order_value' => 29.99,
        ]);

        Order::create([
            'order_number' => 'KU20260102002',
            'customer_id' => $customer2->id,
            'customer_name' => $customer2->name,
            'customer_phone' => $customer2->phone,
            'customer_email' => $customer2->email,
            'delivery_address' => $customer2->address,
            'delivery_lat' => 42.6478,
            'delivery_lng' => 21.1701,
            'status' => 'confirmed',
            'scheduled_date' => today(),
            'courier_id' => $courier->id,
            'payment_method' => 'card',
            'payment_status' => 'paid',
            'order_value' => 54.50,
        ]);

        Order::create([
            'order_number' => 'KU20260110003',
            'customer_name' => 'Arben Hoxha',
            'customer_phone' => '+38344333444',
            'customer_email' => 'arben@example.com',
            'delivery_address' => 'Rruga Bill Clinton 44, Prishtinë',
            'delivery_lat' => 42.6547,
            'delivery_lng' => 21.1622,
            'status' => 'pending',
            'scheduled_date' => today()->addDay(),
            'payment_method' => 'cash',
            'payment_status' => 'pending',
            'order_value' => 45.50,
        ]);

        $this->command->info('✅ Database seeded successfully!');
        $this->command->info('🔑 Test Courier: leart@kuosht.com / courier123');
    }
}
