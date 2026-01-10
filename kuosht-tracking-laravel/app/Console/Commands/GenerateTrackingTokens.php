<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class GenerateTrackingTokens extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'orders:generate-tracking-tokens';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate tracking tokens for orders that don\'t have one';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $orders = \App\Models\Order::whereNull('tracking_token')->get();

        if ($orders->isEmpty()) {
            $this->info('All orders already have tracking tokens.');
            return 0;
        }

        $count = 0;
        foreach ($orders as $order) {
            $order->update([
                'tracking_token' => bin2hex(random_bytes(32))
            ]);
            $count++;
        }

        $this->info("Generated tracking tokens for {$count} orders.");
        return 0;
    }
}
