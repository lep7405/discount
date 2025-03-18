<?php

use App\Repositories\Coupon\CouponRepository;
use App\Repositories\Discount\DiscountRepository;
use App\Services\Report\ReportServiceImp;
use Illuminate\Pagination\LengthAwarePaginator;
uses(\Tests\TestCase::class);
beforeEach(function () {
    $this->discountRepository = Mockery::mock(DiscountRepository::class);
    $this->couponRepository = Mockery::mock(CouponRepository::class);
    $this->reportService = new ReportServiceImp(
        $this->discountRepository,
        $this->couponRepository
    );
});

test('index trả về dữ liệu báo cáo đầy đủ và chính xác', function () {
    $databaseName = 'test_db';
    $filters = [
        'perPageDiscount' => 10,
        'startedAt' => 'desc',
        'perPageCoupon' => 15,
        'timeUsed' => 'asc',
        'status' => '0'
    ];
    $this->discountRepository->shouldReceive('countDiscount')
        ->once()
        ->with($databaseName)
        ->andReturn(100);

    $discountPaginator = mockPaginator(25, 4, 1, 10);
    $this->discountRepository->shouldReceive('getAll')
        ->once()
        ->with($databaseName, Mockery::any())
        ->andReturn($discountPaginator);

    $this->couponRepository->shouldReceive('countCoupons')
        ->once()
        ->with($databaseName)
        ->andReturn(200);

    $couponPaginator = mockPaginator(45, 3, 1, 15);
    $this->couponRepository->shouldReceive('getAll')
        ->once()
        ->with(null, $databaseName, Mockery::any())
        ->andReturn($couponPaginator);

    $discount1 = (object)['coupon' => [
        (object)['times_used' => 5],
        (object)['times_used' => 3]
    ]];
    $discount2 = (object)['coupon' => [
        (object)['times_used' => 0]
    ]];
    $discount3 = (object)['coupon' => []];

    $allDiscounts = collect([$discount1, $discount2, $discount3]);

    $this->discountRepository->shouldReceive('getAllWithCoupon')
        ->once()
        ->with($databaseName)
        ->andReturn($allDiscounts);

    // Gọi phương thức cần test
    $result = $this->reportService->index($filters, $databaseName);

    // Kiểm tra kết quả discount
    expect($result['discountData'])->toBe($discountPaginator)
        ->and($result['totalPagesDiscount'])->toBe(4)
        ->and($result['totalItemsDiscount'])->toBe(25)
        ->and($result['currentPagesDiscount'])->toBe(1)
        ->and($result['totalItems'])->toBe(200)

        ->and($result['couponData'])->toBe($couponPaginator)
        ->and($result['totalPagesCoupon'])->toBe(3)
        ->and($result['totalItemsCoupon'])->toBe(45)
        ->and($result['currentPagesCoupon'])->toBe(1)
        ->and($result['countDiscount'])->toBe(3)
        ->and($result['countDiscountUsed'])->toBe(1)
        ->and($result['countCoupon'])->toBe(3)
        ->and($result['countCouponUsed'])->toBe(8);

    // Kiểm tra kết quả coupon

    // Kiểm tra kết quả thống kê tổng hợp
});

test('handleFiltersDiscount xử lý đúng filter không hợp lệ', function () {
    $countAll = 50;
    $filters = [
        'perPageDiscount' => -1,
        'startedAt' => 'invalid'
    ];

    $result = $this->reportService->handleFiltersDiscount($countAll, $filters);

    expect($result['perPageDiscount'])->toBe(50)
        ->and($result['startedAt'])->toBeNull();
});

test('handleFiltersCoupon xử lý đúng filter không hợp lệ', function () {
    $countAll = 75;
    $filters = [
        'perPageCoupon' => -1,
        'timeUsed' => 'invalid',
        'status' => 'invalid'
    ];

    $result = $this->reportService->handleFiltersCoupon($countAll, $filters);

    expect($result['perPageCoupon'])->toBe(75)
        ->and($result['timeUsed'])->toBeNull()
        ->and($result['status'])->toBeNull();
});
function mockPaginator($total, $lastPage, $currentPage, $perPage) {
    $paginator = Mockery::mock(LengthAwarePaginator::class);
    $paginator->shouldReceive('total')->andReturn($total);
    $paginator->shouldReceive('lastPage')->andReturn($lastPage);
    $paginator->shouldReceive('currentPage')->andReturn($currentPage);
    $paginator->shouldReceive('perPage')->andReturn($perPage);
    return $paginator;
}
