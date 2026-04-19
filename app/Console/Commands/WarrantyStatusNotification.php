<?php

namespace App\Console\Commands;

use App\Enum\WarrantyStatusType;
use App\Mail\WarrantyNearExpiry;
use App\Models\Warranty;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use App\Mail\WarrantyExpired;

class WarrantyStatusNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'warranty:status-notification';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update warranty status to active, near expiry, expired';

    /**
     * Execute the console command.
     * 
     * Updates the status of the warranty and send warrantyStatus mail to the customer
     */
    public function handle()
    {
        $today = now()->today();
        $thirtyDays = now()->addMonth()->endOfDay();

        // update expiration date to expired
        Warranty::whereDate('expiry_date', '<=', $today)
            ->where('status', '!=', WarrantyStatusType::EXPIRED)
            ->whereNotNull('user_id')
            ->chunk(100, function ($warranties) {
                foreach ($warranties as $warranty) {
                    // update status // note if time is managable remove status in column refawctor ui also
                    $warranty->update(['status' => WarrantyStatusType::EXPIRED]);

                    Mail::to($warranty->user->email)->queue(new WarrantyExpired($warranty));
                }
            });

        // update near expiry
        Warranty::whereBetween('expiry_date', [$today, $thirtyDays])
            ->where('status', '!=', WarrantyStatusType::NEAR_EXPIRY)
            ->where('status', '!=', WarrantyStatusType::EXPIRED)
            ->whereNotNull('user_id')
            ->chunk(100, function ($warranties) {
                foreach ($warranties as $warranty) {
                    $warranty->update(['status' => WarrantyStatusType::NEAR_EXPIRY]);

                    Mail::to($warranty->user->email)->queue(new WarrantyNearExpiry($warranty));
                }
            });

        // incase of error mark the updated error if its 30 days up
        Warranty::whereDate('expiry_date', '>', $thirtyDays)
            ->where('status', '!=', WarrantyStatusType::ACTIVE)
            ->update(['status' => WarrantyStatusType::ACTIVE]);
    }
}
