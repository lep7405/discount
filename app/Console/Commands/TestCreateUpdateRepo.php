<?php

namespace App\Console\Commands;

use App\Repositories\Discount\DiscountRepository;
use Illuminate\Console\Command;

class TestCreateUpdateRepo extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:test-create-update-repo';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle(DiscountRepository $discountRepository)
    {
        $data = [
            'name' => 'discount1',
            'type' => 'percentage',
        ];
        $dataUpdate = [
            'name' => 'discount2',
            'type' => 'amount',
        ];
        $discount = $discountRepository->createDiscount($data, 'cs');
        $discountRepository->deleteDiscount($discount->id, 'cs');
        $result = $discountRepository->updateDiscount($dataUpdate, $discount->id, 'cs');
        dd($result);
    }
}
