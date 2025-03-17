<?php

namespace App\Console\Commands;

use App\Models\Coupon;
use Illuminate\Console\Command;

class TestGetCouponByDiscountIdAndCode extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:test-get-coupon-by-discount-id-and-code';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $coupon = Coupon::on('cs')
            ->where('discount_id', 100)
            ->where('code', 'like', 'GENAUTO%')
            ->get();
        dd(json_encode($coupon, JSON_PRETTY_PRINT));

    }
}
