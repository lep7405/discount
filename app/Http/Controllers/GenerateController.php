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
        $config = config('database.connections');
        $this->configDatabase = array_keys(
            Arr::except($config, ['mysql', 'mariadb', 'pgsql', 'sqlsrv', 'sqlite'])
        );
        $this->apps = array_map(fn ($db) => $db['app_name'] ?? '', $config);

        $this->apiKey = config('Discount_manager.customerio.apiKey');

        $this->siteId = config('Discount_manager.customerio.siteId');

        $this->appKey = config('Discount_manager.customerio.appKey');
    }

    public function index(Request $request,GenerateService $generateService)
    {
        $data = $generateService->index($request->query());
        return view('admin.generates.index', [
            'generateData' => $data['generateData'],
            'totalPages' => $data['totalPages'],
            'totalItem' => $data['totalItems'],
            'currentPage' => $data['currentPages'] ?? 1,
            'perPage' => $request->query('perPage', 5),
            'search' => $request->query('search'),
            'status' => $request->query('status'),
        ]);
    }

    public function create(GenerateService $generateService)
    {
        return view('admin.generates.create', [
            'discountData' => $generateService->create($this->configDatabase),
            'apps' => $this->apps,
        ]);
    }

    public function store(CreateGenerateRequest $request,GenerateService $generateService): RedirectResponse
    {
        $generateService->store($request->validationData());

        return redirect()->route('admin.indexGenerate')->with('success', 'Created Generate Success');
    }

    public function edit(int $id, GenerateService $generateService)
    {
        $arr = $generateService->edit($id,$this->configDatabase);

        $generate = $arr['generate'];

        $generate->conditions = json_encode($generate->conditions); // chỗ này đang chữa cháy thôi chứ sau tìm hiểu được thì phải xóa nó đi

        return view('admin.generates.update', [
            'discountData' => $arr['discountData'],
            'apps' => $this->apps,
            'generate' => $arr['generate'],
            'status_del' => $arr['status_del'],
            'generate_url' => url("/coupon/{$id}/"),
            'private_generate_url' => url("/coupon/private/{$id}/"),
        ]);
    }

    public function update(int $id, UpdateGenerateRequest $request, GenerateService $generateService): RedirectResponse
    {
        $generateService->update($id, $request->validationData());

        return redirect()->route('admin.indexGenerate')->with('success', 'Updated Generate Success');
    }

    public function destroy(int $id, GenerateService $generateService)
    {
        $generateService->destroy($id);

        return redirect()->route('admin.indexGenerate')->with('success', 'Deleted Generate Success');
    }

    public function changeStatus(int $id, GenerateService $generateService)
    {
        $generateService->changeStatus($id);

        return back()->with('success', 'Change Status Generate Success');
    }

    public function generateCoupon(int $generateId, $timeStamp, $shopId, GenerateService $generateService)
    {
        $data = $generateService->generateCoupon($generateId, $timeStamp, $shopId);

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

    public function privateGenerateCoupon(int $generateId, $shopName,Request $request, GenerateService $generateService)
    {
        $data = $generateService->privateGenerateCoupon($request->ip(), $generateId, $shopName);

        return response()->json($data);
    }

    public function createCouponFromAffiliatePartner(string $appCode, string $shopName,Request $request,  GenerateService $generateService)
    {
        $coupon = $generateService->createCouponFromAffiliatePartner($request->input(), $appCode, $shopName);

        response()->json([
            'message' => 'Coupon created successfully',
            'coupon' => $coupon,
        ]);
    }
}
