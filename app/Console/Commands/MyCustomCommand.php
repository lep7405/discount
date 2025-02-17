<?php

namespace App\Console\Commands;

use App\Models\Discount;
use App\Models\Generate;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MyCustomCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:test';

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
        //        $search = '10';
        //        $cs = DB::connection('cs')->table('discounts');
        //        $affiliate = DB::connection('affiliate')->table('discounts');
        //        //        dd($affiliate->first());
        //        // Truy vấn sử dụng join từ Generate và bảng discounts
        //        $data1 = DB::table('generates')
        //            ->leftJoinSub($affiliate, 'discounts_affiliate', function ($join) {
        //                $join->on('generates.discount_id', '=', 'discounts_affiliate.id');  // Sử dụng bí danh 'discounts_affiliate'
        //            })
        //            ->first();
        //        //        dd(json_encode($data1));
        //        $data = DB::table('generates')
        ////            ->leftJoinSub($cs, 'discounts_cs', function($join) {
        ////                $join->on('generates.discount_id', '=', 'discounts_cs.id');  // Sử dụng bí danh 'discounts_cs'
        ////            })
        //            ->leftJoinSub($affiliate, 'discounts_affiliate', function ($join) {
        //                $join->on('generates.discount_id', '=', 'discounts_affiliate.id');  // Sử dụng bí danh 'discounts_affiliate'
        //            })
        //            ->select('generates.id', 'generates.app_name', 'generates.discount_id', 'generates.conditions', 'generates.expired_range', 'generates.limit', 'generates.header_message', 'generates.success_message', 'generates.used_message', 'generates.fail_message', 'generates.app_url', 'generates.status', 'generates.created_at', 'generates.updated_at')  // Chọn rõ các cột
        //
        ////            ->orWhere('conditions', 'like', "%{$search}%")
        //////            ->orWhere('discounts_cs.name', 'like', "%{$search}%")
        ////            ->orWhere('discounts_affiliate.name', 'like', "%{$search}%")
        //            ->first();

        // Debug dữ liệu lấy được

        $search = 'curren';
        $status = 0;
        $cs = DB::connection('cs')->table('discounts')->select('id as ids', 'name');
        $affiliate = DB::connection('affiliate')->table('discounts')->select('id as ids', 'name');
        $data = Generate::query()
//                ->select('generates.*')
//                ->addSelect(DB::raw(" CASE
//             WHEN app_name = 'cs' THEN 'currency switcher'
//            ELSE app_name
//            end as full_name
//            "))
            ->leftJoinSub($affiliate, 'discounts_affiliate', function ($join) {
                $join->on('generates.discount_id', '=', 'discounts_affiliate.ids');
            })
            ->leftJoinSub($cs, 'discounts_cs', function ($join) {
                $join->on('generates.discount_id', '=', 'discounts_cs.ids');
            })
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('conditions', 'like', "%{$search}%")
                        ->orWhere(function ($sub) use ($search) {
                            if (is_numeric($search)) {
                                $sub->where('id', $search);
                            }
                        })
                        ->orWhereRaw("(CASE
                    WHEN app_name = 'cs' THEN 'currency switcher'
                    WHEN app_name = 'affiliate' THEN 'affiliate'
                    ELSE app_name
                END) LIKE ?", ["%{$search}%"])
                        ->orWhere('conditions', 'like', "%{$search}%")
                        ->orWhere('expired_range', 'like', "%{$search}%")
                        ->orWhere('app_url', 'like', "%{$search}%")
                        ->orWhere('discounts_cs.name', 'like', "%{$search}%")
                        ->orWhere('discounts_affiliate.name', 'like', "%{$search}%");
                });
            })
            ->when($status !== null, function ($query) use ($status) {
                $query->where('status', $status);
            })->first();

        dd(json_encode($data));

    }
}
