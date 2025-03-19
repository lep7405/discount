<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateGenerateRequest;
use App\Http\Requests\UpdateGenerateRequest;
use App\Models\Generate;
use App\Services\Generate\GenerateService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class GenerateController extends Controller
{
    protected array $configDatabase;

    protected array $apps;

    public function __construct()
    {
        $config = config('database.connections');
        $this->configDatabase = array_keys(
            Arr::except($config, ['mysql', 'mariadb', 'pgsql', 'sqlsrv', 'sqlite'])
        );
        $this->apps = array_map(fn ($db) => $db['app_name'] ?? '', $config);
    }

    public function index(Request $request,GenerateService $generateService)
    {
        $data = $generateService->index($request->query());
        return view('admin.generates.index', [
            'generateData' => $data['generateData'],
            'totalPages' => $data['totalPages'],
            'totalItems' => $data['totalItems'],
            'totalGenerates' => $data['totalGenerates'],
            'currentPage' => $data['currentPages'] ?? 1,
            'perPage' => $request->query('perPage', 5),
            'search' => $request->query('search') ?? null,
            'status' => $request->query('status') ?? null,
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

    public function generateCoupon(int $generateId, $timestamp, $shopId, GenerateService $generateService)
    {
        $data = $generateService->generateCoupon($generateId, $timestamp, $shopId);

        return view('customer.coupon.layout',
            [
                'headerMessage' => Arr::get($data, 'headerMessage'),
                'contentMessage' => Arr::get($data, 'contentMessage'),
                'reasons' => Arr::get($data, 'reasons'),
                'appUrl' => Arr::get($data, 'appUrl'),
                'generateId' => Arr::get($data, 'generateId'),
                'customFail' => Arr::get($data, 'customFail'),
                'extendMessage' => Arr::get($data, 'extendMessage'),
                'couponCode' => Arr::get($data, 'couponCode'),
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
