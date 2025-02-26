<?php

namespace App\Console\Commands;

use App\Models\Coupon;
use Illuminate\Console\Command;

class TestCreateCoupon extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:test-create-coupon';

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
        $data = [
            'code' => 'code116',
            'discount_id' => 5,
            'shop' => 'shop3',
            'automatic' => true,
        ];
        Coupon::on('cs')->create($data);
    }
}
