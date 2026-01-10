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
        Schema::table('orders', function (Blueprint $table) {
            $table->string('tracking_token', 64)->unique()->nullable()->after('order_number');
            $table->timestamp('eta')->nullable()->after('scheduled_date');
            $table->decimal('distance_to_delivery', 8, 2)->nullable()->after('eta');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['tracking_token', 'eta', 'distance_to_delivery']);
        });
    }
};
