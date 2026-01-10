<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Courier;
use Illuminate\Support\Facades\Hash;

class ResetCourierPasswords extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'couriers:reset-passwords';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reset all courier passwords to courier123';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Resetting courier passwords...');

        $couriers = Courier::all();

        foreach ($couriers as $courier) {
            $courier->password = Hash::make('courier123');
            $courier->save();
            $this->line("✅ Reset password for: {$courier->name} ({$courier->email})");
        }

        $this->info("\n✅ All courier passwords have been reset to: courier123");

        return 0;
    }
}
