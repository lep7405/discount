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

        return view('admin.generates.coupon', compact(
            [

            ]
        ));
    }

    public function privateGenerateCoupon(Request $request, $generateId, $shopName, GenerateService $generateService)
    {
        $data = $generateService->privateGenerateCoupon($request->ip(), $generateId, $shopName);

        return response()->json($data);
    }
}
