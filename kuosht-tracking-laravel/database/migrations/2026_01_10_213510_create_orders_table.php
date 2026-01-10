<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number', 50)->unique();
            $table->foreignId('customer_id')->nullable()->constrained('customers')->onDelete('set null');
            $table->string('customer_name');
            $table->string('customer_phone', 20);
            $table->string('customer_email')->nullable();
            $table->text('delivery_address');
            $table->decimal('delivery_lat', 10, 7);
            $table->decimal('delivery_lng', 10, 7);
            $table->enum('status', [
                'pending', 'confirmed', 'assigned', 'picked_up',
                'in_transit', 'nearby', 'delivered', 'failed',
                'cancelled', 'rescheduled'
            ])->default('pending');
            $table->date('scheduled_date');
            $table->time('scheduled_time_start')->nullable();
            $table->time('scheduled_time_end')->nullable();
            $table->foreignId('courier_id')->nullable()->constrained('couriers')->onDelete('set null');
            $table->timestamp('assigned_at')->nullable();
            $table->timestamp('picked_up_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->text('delivery_notes')->nullable();
            $table->string('signature_image')->nullable();
            $table->enum('payment_method', ['cash', 'card', 'online'])->default('cash');
            $table->enum('payment_status', ['pending', 'paid', 'refunded'])->default('pending');
            $table->decimal('order_value', 10, 2)->default(0);
            $table->integer('priority')->default(0);
            $table->text('special_instructions')->nullable();
            $table->timestamps();

            // Indexes
            $table->index('status');
            $table->index('scheduled_date');
            $table->index(['courier_id', 'status']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
