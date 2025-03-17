<?php

namespace App\Console\Commands;

use App\Models\Discount;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class TestDiscountMonth extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:test-discount-month';

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
        $data = DB::connection('cs')->table('discounts')->insert([
            'name' => 'name1',
            'value' => 'value1',
            'type' => 'amount',
            'started_at' => null,
            'expired_at' => null,
            'usage_limit' => 1000,
            'trial_days' => 100,
            'discount_month' => 10,
            'discount1' => 1,
        ]);

        $data = Discount::on('cs')->create([
            'name' => 'name1',
            'value' => 100,
            'type' => 'amount',
            'started_at' => null,
            'expired_at' => null,
            'usage_limit' => 1000,
            'trial_days' => 100,
            'discount_month' => 10,
            'discount1' => 1,
        ]);
        dd($data);
    }
}
