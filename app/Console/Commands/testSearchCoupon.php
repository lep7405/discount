<?php

namespace App\Console\Commands;

use App\Models\Coupon;
use Illuminate\Console\Command;

class testSearchCoupon extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:test-search-coupon';

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
        $status = null;
        $arrange_times_used = null;
        $search = 'coupon_2_1';
        $databaseConnection = 'cs'; // Đặt kết nối cơ sở dữ liệu ở đây (ví dụ: 'cs')

        // Sử dụng mô hình Eloquent với kết nối linh động
        $data = Coupon::on($databaseConnection) // Chỉ định kết nối cơ sở dữ liệu linh động ở đây
            ->with(['discount:id,name'])
            ->when($search, function ($query) use ($search) {
                $query->where('code', 'like', "%{$search}%")
                    ->orWhere('shop', 'like', "%{$search}%")
                    ->orWhereHas('discount', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    })
                    ->orWhere('times_used', $search);
            })
            ->when($status !== null, function ($query) use ($status) {
                $query->where('status', $status);
            })
            ->when($arrange_times_used, function ($query) use ($arrange_times_used) {
                $query->orderBy('times_used', $arrange_times_used);
            })
            ->get();
        dd($data);

        return $data->toArray();
    }
}
