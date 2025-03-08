<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateGenerateRequest;
use App\Http\Requests\UpdateGenerateRequest;
use App\Services\Generate\GenerateService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class GenerateController extends Controller
{
    protected array $configDatabase;

    protected array $apps;

    private string $apiKey;

    private string $siteId;

    private string $appKey;

    public function __construct()
    {
        // Lấy danh sách tất cả các kết nối cơ sở dữ liệu từ file config/database.php
        $config = config('database.connections');

        $this->configDatabase = array_keys(
            Arr::except($config, ['mysql', 'mariadb', 'pgsql', 'sqlsrv', 'sqlite'])
        );

        // Lấy danh sách ứng dụng từ từng database, nếu không có 'app_name' thì mặc định là chuỗi rỗng
        $this->apps = array_map(fn ($db) => $db['app_name'] ?? '', $config);

        $this->apiKey = config('Discount_manager.customerio.apiKey');
        $this->siteId = config('Discount_manager.customerio.siteId');
        $this->appKey = config('Discount_manager.customerio.appKey');
    }

    public function index(GenerateService $generateService, Request $request)
    {
        $data = $generateService->index($request->query());

        return view('admin.generates.index', [
            'generateData' => $data['generateData'],
            'totalPages' => $data['total_pages'],
            'totalItem' => $data['total_items'],
            'currentPage' => $data['current_pages'] ?? 1,
            'per_page' => $request->query('per_page', 5),
            'search' => $request->query('search'),
            'status' => $request->query('status'),
        ]);
    }

    public function create(GenerateService $generateService, CreateGenerateRequest $request): RedirectResponse
    {
        $generateService->create($request->validationData());

        return redirect()->route('admin.get_generate')->with('success', 'Created Generate Success');
    }

    public function update($id, UpdateGenerateRequest $request, GenerateService $generateService): RedirectResponse
    {
        $generateService->update($id, $request->validationData());

        return redirect()->route('admin.get_generate')->with('success', 'Updated Generate Success');
    }

    public function destroy($id, GenerateService $generateService): RedirectResponse
    {
        $generateService->destroy($id);

        return redirect()->route('admin.get_generate')->with('success', 'Deleted Generate Success');
    }

    public function showCreate(GenerateService $generateService)
    {
        return view('admin.generates.create', [
            'discountData' => $generateService->showCreate($this->configDatabase),
            'apps' => $this->apps,
        ]);
    }

    public function showUpdate($id, GenerateService $generateService)
    {
        $arr = $generateService->showUpdate($id);

        return view('admin.generates.update', [
            'discountData' => $generateService->showCreate($this->configDatabase),
            'apps' => $this->apps,
            'generate' => $arr['generate'],
            'status_del' => $arr['status_del'],
            'generate_url' => url("/coupon/{$id}/"),
            'current_discountDB' => $arr['discount'],
            'private_generate_url' => url("/coupon/private/{$id}/"),
        ]);
    }

    public function changeStatus($id, GenerateService $generateService): RedirectResponse
    {
        $generateService->changeStatus($id);

        return back()->with('success', 'Change Status Generate Success');
    }

    public function generateCoupon($generate_id, $timestamp, $shop_id, GenerateService $generateService)
    {
        $data = $generateService->generateCoupon($generate_id, $timestamp, $shop_id);

        return view('customer.coupon.layout',
            [
                'header_message' => Arr::get($data, 'header_message'),
                'content_message' => Arr::get($data, 'content_message'),
                'reasons' => Arr::get($data, 'reasons'),
                'app_url' => Arr::get($data, 'app_url'),
                'generate_id' => Arr::get($data, 'generate_id'),
                'custom_fail' => Arr::get($data, 'custom_fail'),
                'extend_message' => Arr::get($data, 'extend_message'),
                'coupon_code' => Arr::get($data, 'coupon_code'),
            ]
        );
    }

    public function privateGenerateCoupon(Request $request, $generateId, $shopName, GenerateService $generateService)
    {
        $data = $generateService->privateGenerateCoupon($request->ip(), $generateId, $shopName);

        return response()->json($data);
    }

    public function createCouponFromAffiliatePartner(Request $request, string $appCode, string $shopName, GenerateService $generateService)
    {
        $coupon = $generateService->createCouponFromAffiliatePartner($request->input(), $appCode, $shopName);
        response()->json([
            'message' => 'Coupon created successfully',
            'coupon' => $coupon,
        ]);
    }

    //test
    public function test1(GenerateService $generateService)
    {
        $data = $generateService->test1();
        //        return response()->json([
        //            'shop'=>Arr::get($data, 'shop'),
        //            'name'=>$data['name'],
        //        ]);
        //        return response()->json(
        //            Arr::only($data, ['shop','name','code'])
        //        );
        dd(Arr::only($data, [
            'shop', 'code', 'name',
        ]));

        return view('test.test1', Arr::only($data, [
            'shop', 'code', 'name',
        ]));
    }

    public function test2()
    {

        return view('test.test2', compact('couponData'));
    }

    public function test3()
    {
        $conditions = ['fg&notinstalledyet||sl&notinstalledyet', 'sw&charged'];
        if ($conditions) {
            $prefix_app = [
                'qv' => 'Quick View',
                'fg' => 'Free gift',
                'pp' => 'Promotion Popup',
                'sl' => 'Store Locator',
                'sp' => 'Store Pickup',
                'bn' => 'Banner Slider',
                'cs' => 'Currency Switcher',
                'pl' => 'Product Label',
                'ca' => 'Customer Attribute',
                'sw' => 'Spin To Win',
                'io' => 'Smart Image Optimizer',
            ];
            foreach ($conditions as $cd) {
                $arr_or = explode('||', $cd); // Tách trên 1 hàng các điều kiện OR
                $text_or = '';
                for ($i = 0; $i < count($arr_or); $i++) {
                    $arr_con = explode('&', $arr_or[$i]); // Dạng của điều kiện name&status

                    $name_status = $arr_con[0] . '_status';
                    dd($name_status);
                    $status = $arr_con[1];
                    // Nếu không có app_status
                    // Hoặc app_status khác
                    // thì lưu text.
                    // Nếu không thì text = ""

                    if ($customer_status == $status) {
                        $text_or = '';
                        break;
                    } else {
                        $text_or .= '<p>';
                        if (count($arr_or) > 1) {
                            $text_or .= "<strong class='or_status'>OR</strong>";
                        }
                        if ($status == 'notinstalledyet') {
                            $text_or .= "<span class='app_status'> " . $prefix_app[$arr_con[0]] . "</span> must be <span class='app_status'>Not Installed yet</span></p>";
                        } else {
                            $text_or .= "<span class='app_status'> " . $prefix_app[$arr_con[0]] . "</span> must be <span class='app_status'>" . $status . '</span></p>';
                        }

                    }
                }

                // Nếu có $text_or thì chứng tỏ điều kiện này ko thoả mãn. Break luôn.
                if ($text_or) {
                    $text = $text_or;
                    break;
                }

            }
        }
    }
}
