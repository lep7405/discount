<?php

use App\Repositories\Discount\DiscountRepository;
use App\Services\DashBoard\DashBoardServiceImp;
use Illuminate\Support\Collection;

uses(\Tests\TestCase::class);

beforeEach(function () {
    $this->discountRepository = Mockery::mock(DiscountRepository::class);
    $this->dashboardService = new DashBoardServiceImp($this->discountRepository);

    // Cấu hình giả lập cho config
    config([
        'database.connections.app1' => ['app_name' => 'Test App 1'],
        'database.connections.app2' => ['app_name' => 'Test App 2'],
        'database.connections.mysql' => ['app_name' => 'MySQL Default']
    ]);
});

afterEach(function () {
    Mockery::close();
});

test('index trả về dữ liệu dashboard đầy đủ và chính xác', function () {
    $databases = ['app1', 'app2'];

    // Tạo dữ liệu mẫu cho app1
    $coupon1App1 = (object)['id' => 1, 'times_used' => 5];
    $coupon2App1 = (object)['id' => 2, 'times_used' => 3];
    $discount1App1 = (object)[
        'id' => 1,
        'name' => 'Discount 1 App1',
        'coupon' => collect([$coupon1App1, $coupon2App1])
    ];

    $coupon3App1 = (object)['id' => 3, 'times_used' => 0];
    $discount2App1 = (object)[
        'id' => 2,
        'name' => 'Discount 2 App1',
        'coupon' => collect([$coupon3App1])
    ];

    $discount3App1 = (object)[
        'id' => 3,
        'name' => 'Discount 3 App1',
        'coupon' => collect([])
    ];

    $app1Discounts = collect([$discount1App1, $discount2App1, $discount3App1]);

    // Tạo dữ liệu mẫu cho app2
    $coupon1App2 = (object)['id' => 4, 'times_used' => 7];
    $discount1App2 = (object)[
        'id' => 4,
        'name' => 'Discount 1 App2',
        'coupon' => collect([$coupon1App2])
    ];

    $app2Discounts = collect([$discount1App2]);

    // Mockup repository cho app1
    $this->discountRepository->shouldReceive('getAllDiscountsWithCoupon')
        ->once()
        ->with('app1')
        ->andReturn($app1Discounts);

    // Mockup repository cho app2
    $this->discountRepository->shouldReceive('getAllDiscountsWithCoupon')
        ->once()
        ->with('app2')
        ->andReturn($app2Discounts);

    // Gọi phương thức cần test
    $result = $this->dashboardService->index($databases);

    // Kiểm tra kết quả
    expect($result)->toBeArray()
        ->and($result['discountData'])->toHaveCount(4)
        ->and($result['couponData'])->toHaveCount(4)
        ->and($result['countDiscountUsed'])->toBe(2)
        ->and($result['countCouponUsed'])->toBe(15)
        ->and($result['apps'])->toHaveCount(2)
        ->and($result['dashboardApps'])->toHaveCount(2)
        ->and($result['dashboardApps'][0])->toMatchArray([
            'db' => 'app1',
            'appName' => 'Test App 1',
            'countDiscount' => 3,
            'countCoupon' => 3,
            'usedCoupons' => 8,
            'countCouponUsed' => 8,
        ])
        ->and($result['dashboardApps'][1])->toMatchArray([
            'db' => 'app2',
            'appName' => 'Test App 2',
            'countDiscount' => 1,
            'countCoupon' => 1,
            'usedCoupons' => 7,
            'countCouponUsed' => 7,
        ]);
});

test('index xử lý đúng khi repository trả về lỗi', function () {
    $databases = ['app1', 'app2'];

    // Mockup repository cho app1 thành công
    $discount1App1 = (object)[
        'id' => 1,
        'name' => 'Discount 1 App1',
        'coupon' => collect([(object)['id' => 1, 'times_used' => 5]])
    ];
    $app1Discounts = collect([$discount1App1]);

    $this->discountRepository->shouldReceive('getAllDiscountsWithCoupon')
        ->once()
        ->with('app1')
        ->andReturn($app1Discounts);

    // Mockup repository cho app2 trả về lỗi
    $this->discountRepository->shouldReceive('getAllDiscountsWithCoupon')
        ->once()
        ->with('app2')
        ->andThrow(new Exception('Connection error'));

    // Gọi phương thức cần test
    $result = $this->dashboardService->index($databases);

    // Kiểm tra kết quả
    expect($result['dashboardApps'][0]['countDiscount'])->toBe(1)
        ->and($result['dashboardApps'][1]['countDiscount'])->toBe(0)
        ->and($result['countDiscountUsed'])->toBe(1)
        ->and($result['countCouponUsed'])->toBe(5);
});

test('getAppNames trả về danh sách các ứng dụng đúng định dạng', function () {
    // Config đã được thiết lập trong beforeEach

    // Truy cập phương thức private bằng reflection
    $reflection = new ReflectionClass($this->dashboardService);
    $method = $reflection->getMethod('getAppNames');
    $method->setAccessible(true);

    $result = $method->invoke($this->dashboardService);

    expect($result)->toBeArray()
        ->toHaveCount(2)
        ->and($result)->toHaveKeys(['app1', 'app2'])
        ->and($result['app1'])->toBe('Test App 1')
        ->and($result['app2'])->toBe('Test App 2')
        ->and($result)->not->toHaveKey('mysql');
});
