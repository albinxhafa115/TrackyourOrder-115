<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class SendTestEmail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'email:send-tracking';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send tracking email to first order customer';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $order = \App\Models\Order::first();

        if (!$order) {
            $this->error('No orders found in database.');
            return 1;
        }

        if (!$order->customer_email) {
            $this->error('Order does not have customer email.');
            return 1;
        }

        try {
            $order->sendTrackingEmail();
            $this->info("✅ Tracking email sent successfully to: {$order->customer_email}");
            $this->info("📦 Order: {$order->order_number}");
            $this->info("🔗 Tracking URL: {$order->trackingUrl}");
            return 0;
        } catch (\Exception $e) {
            $this->error("❌ Failed to send email: " . $e->getMessage());
            return 1;
        }
    }
}
