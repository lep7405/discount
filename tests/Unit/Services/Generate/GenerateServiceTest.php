<?php

namespace Tests\Unit\Services\Generate;

use App\Exceptions\DiscountException;
use App\Exceptions\GenerateException;
use App\Exceptions\NotFoundException;
use App\Models\Coupon;
use App\Models\Discount;
use App\Models\Generate;
use App\Repositories\Coupon\CouponRepository;
use App\Repositories\Discount\DiscountRepository;
use App\Repositories\Generate\GenerateRepository;
use App\Services\Generate\GenerateService;
use Customerio\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Mockery;

uses(\Tests\TestCase::class);

beforeEach(function () {
    $this->generateRepository = Mockery::mock(GenerateRepository::class);
    $this->discountRepository = Mockery::mock(DiscountRepository::class);
    $this->couponRepository = Mockery::mock(CouponRepository::class);
    $this->generateService = app()->make(GenerateService::class, [
        'generateRepository' => $this->generateRepository,
        'discountRepository' => $this->discountRepository,
        'couponRepository' => $this->couponRepository,
    ]);
    config(['database.connections.db1.app_name' => 'Application One']);
    config(['database.connections.db2.app_name' => 'Application Two']);
    Coupon::on('cs')->delete();
    Discount::on('cs')->delete();
    Generate::query()->delete();
});
//test index
test('index returns correct data structure with pagination', function () {
    $filters = ['perPage' => 5];
    $totalCount = 15;

    // Giả lập dữ liệu generate
    $generateData = new Collection([
        new Generate([
            'id' => 1,
            'app_name' => 'db1',
            'discount_id' => 10,
            'status' => 1,
        ]),
        new Generate([
            'id' => 2,
            'app_name' => 'db1',
            'discount_id' => 20,
            'status' => 0,
        ]),
    ]);

    // Tạo paginator
    $paginatedData = new LengthAwarePaginator(
        $generateData,
        $totalCount,
        5,
        1
    );

    // Thiết lập mock
    $this->generateRepository->shouldReceive('countGenerate')
        ->once()
        ->andReturn($totalCount);

    $this->generateRepository->shouldReceive('getAll')
        ->once()
        ->with($filters)
        ->andReturn($paginatedData);

    // Mock findDiscountsByIdsAndApp
    $this->discountRepository->shouldReceive('findByIdsAndApp')
        ->once()
        ->withArgs(function ($ids, $appName) {
            return is_array($ids)
                && $ids == [10, 20]
                && $appName === 'db1';
        })
        ->andReturn(collect([
            (object) ['id' => 10, 'name' => 'Discount 1', 'expired_at' => null],
            (object) ['id' => 20, 'name' => 'Discount 2', 'expired_at' => now()->addDay()],
        ]));

    $result = $this->generateService->index($filters);

    // Kiểm tra kết quả
    expect($result)->toBeArray()
        ->toHaveKeys(['generateData', 'totalPages', 'totalItems', 'currentPages'])
        ->and($result['totalPages'])->toBe($paginatedData->lastPage())
        ->and($result['totalItems'])->toBe($paginatedData->total())
        ->and($result['currentPages'])->toBe($paginatedData->currentPage())
        ->and($result['generateData'])->toHaveCount(2);

    // Kiểm tra các giá trị trong generateData

    foreach ($result['generateData'] as $item) {
        expect($item)->toHaveKey('app_name')->toHaveKey('db_name')->toHaveKey('discount_name')
            ->and($item['app_name'])->toBe('Application One')
            ->and($item['db_name'])->toBe('db1');
    }
});
test('index handles -1 perPage value by using all records', function () {
    $filters = ['perPage' => -1];
    $totalCount = 15;

    $this->generateRepository->shouldReceive('countGenerate')
        ->once()
        ->andReturn($totalCount);

    $this->generateRepository->shouldReceive('getAll')
        ->once()
        ->withArgs(function ($args) use ($totalCount) {
            return $args['perPage'] === $totalCount;
        })
        ->andReturn(new LengthAwarePaginator(
            collect([]),
            $totalCount,
            perPage: $totalCount,
            currentPage: 1
        ));

    $this->discountRepository->shouldReceive('findByIdsAndApp')
        ->andReturn(collect([]));

    $result = $this->generateService->index($filters);

    expect($result)->toBeArray()
        ->and($result['totalItems'])->toBe($totalCount);
});
test('index throws NotFoundException when discount is not found', function () {
    $filters = ['perPage' => 5];
    $totalCount = 1;

    // Tạo generate với discount_id không tồn tại
    $generateData = new Collection([
        new Generate([
            'id' => 1,
            'app_name' => 'app1',
            'discount_id' => 999, // ID không tồn tại
            'status' => 1,
        ]),
    ]);

    $paginatedData = new LengthAwarePaginator(
        $generateData,
        $totalCount,
        5,
        1
    );

    $this->generateRepository->shouldReceive('countGenerate')
        ->andReturn($totalCount);

    $this->generateRepository->shouldReceive('getAll')
        ->andReturn($paginatedData);

    // Trả về collection rỗng để mô phỏng không tìm thấy discount
    $this->discountRepository->shouldReceive('findByIdsAndApp')
        ->andReturn(collect([]));

    // Phải ném ra NotFoundException
    $this->expectException(NotFoundException::class);

    $this->generateService->index($filters);
});
//test show create
test('showCreate gộp dữ liệu từ nhiều database và thêm thông tin database vào mỗi discount', function () {
    $databaseNames = ['db1', 'db2'];

    // Mock dữ liệu từ discount repository
    $this->discountRepository->shouldReceive('getAllDiscountIdAndName')
        ->once()
        ->with('db1')
        ->andReturn([
            ['id' => 1, 'name' => 'Discount 1'],
            ['id' => 2, 'name' => 'Discount 2'],
        ]);

    $this->discountRepository->shouldReceive('getAllDiscountIdAndName')
        ->once()
        ->with('db2')
        ->andReturn([
            ['id' => 3, 'name' => 'Discount 3'],
        ]);

    // Gọi phương thức cần test
    $result = $this->generateService->create($databaseNames);
    // Kiểm tra kết quả
    expect($result)->toBeArray()->toHaveCount(3)
        ->and($result[0]['id'])->toBe(1)
        ->and($result[0]['name'])->toBe('Discount 1')
        ->and($result[0]['databaseName'])->toBe('db1')
        ->and($result[0]['appName'])->toBe('Application One');
});
//test create
test('create generate fails when discount not found', function () {
    $discounIdNotExist = 1000000000;
    $this->discountRepository->shouldReceive('findById')
        ->with($discounIdNotExist, 'cs')
        ->andReturn(null);
    $data = [
        'discount_app' => $discounIdNotExist . '&cs',
    ];
    expect(fn () => $this->generateService->store($data))
        ->toThrow(NotFoundException::class, 'Discount not found');
});
test('create generate fails when discount expired', function () {
    $discount = (object) [
        'id' => 1,
        'name' => 'discount',
        'type' => 'percentage',
        'expired_at' => Carbon::now()->subDays(1),
    ];
    $this->discountRepository->shouldReceive('findById')
        ->with($discount->id, 'cs')
        ->andReturn($discount);
    $data=[
        'discount_app' => $discount->id . '&cs',
        'expired_range' => 50,
        'app_url' => 'http://localhost:8000/admin',
    ];
    expect(fn () => $this->generateService->store($data))->toThrow(function (DiscountException $e){
        expect($e->getErrors()['error'])->toBe('Discount expired');
    });
});
test('create generate fails when generate existed discount_id and app_name', function () {
    $discount = Discount::factory()->make([
        'id' => 1,
        'name' => 'discount',
        'type' => 'percentage',
        'expired_at' => now()->addDays(1),
    ]);
    $generate = Generate::factory()->make([
        'id' => 1,
        'discount_id' => 1,
        'app_name' => 'cs',
        'app_url' => 'http://localhost:8000/admin',
    ]);
    $this->discountRepository->shouldReceive('findById')
        ->with($discount->id, 'cs')
        ->andReturn($discount);
    $this->generateRepository->shouldReceive('findByDiscountIdAndAppName')
        ->with($discount->id, 'cs')
        ->andReturn($generate);
    $data = [
        'discount_app' => $discount->id . '&cs',
        'expired_range' => 50,
        'app_url' => 'http://localhost:8000/admin',
    ];
    expect(fn () =>  $this->generateService->store($data))
        ->toThrow(function (GenerateException $e) {
        expect($e->getErrors()['error'])->toBe('Generate existed discount_id and app_name');
    });
});
test('create generate success', function () {
    $discount = Discount::factory()->make([
        'id' => 1,
        'name' => 'discount',
        'type' => 'percentage',
        'expired_at' => Carbon::now()->addDays(1),
    ]);

    $this->discountRepository->shouldReceive('findById')
        ->with($discount->id, 'cs')
        ->andReturn($discount);

    $this->generateRepository->shouldReceive('findByDiscountIdAndAppName')
        ->with($discount->id, 'cs')
        ->andReturn(null);

    $data = [
        'discount_app' => $discount->id . '&cs',
        'expired_range' => 50,
        'app_url' => 'http://localhost:8000/admin/generates_new',
        'limit' => 5,
        'condition_object' => '[{"id":1,"apps":[{"name":"fg","status":"notinstalledyet"}]},{"id":2,"apps":[{"name":"pp","status":"uninstalled"}]}]',
        'header_message' => 'header',
        'success_message' => 'success',
        'used_message' => 'used',
        'fail_message' => 'fails',
        'extend_message' => 'extend',
        'reason_expired' => 'time',
        'reason_limit' => 'limit',
        'reason_condition' => 'notMatch',
    ];
    $this->generateRepository->shouldReceive('createGenerate')
        ->once()
        ->withArgs(function ($actualData) use ($discount) {
            return isset($actualData['app_name'])
                && $actualData['app_name'] === 'cs'
                && isset($actualData['discount_id'])
                && $actualData['discount_id'] == $discount->id
                && isset($actualData['expired_range'])
                && $actualData['expired_range'] === 50;
        })
        ->andReturn([
            'app_name' => 'cs',
            'discount_id' => $discount->id,
            'conditions' => ['fg&notinstalledyet', 'pp&uninstalled'],
            'success_message' => ['message' => 'success', 'extend' => 'extend'],
            'fail_message' => ['message' => 'fails', 'reason_expired' => 'time', 'reason_limit' => 'limit', 'reason_condition' => 'notMatch'],
        ]);

    $result = $this->generateService->store($data);

    $this->assertEquals('cs', $result['app_name']);
    $this->assertEquals(['fg&notinstalledyet', 'pp&uninstalled'], $result['conditions']);
    $this->assertEquals(['message' => 'success', 'extend' => 'extend'], $result['success_message']);
    $this->assertEquals(['message' => 'fails', 'reason_expired' => 'time', 'reason_limit' => 'limit', 'reason_condition' => 'notMatch'], $result['fail_message']);
});
//test condition
test('test function handle condition', function () {
    $condition = '[{"id":1,"apps":[{"name":"fg","status":"notinstalledyet"},{"name":"ca","status":"installed"}]},{"id":2,"apps":[{"name":"pl","status":"charged"}]}]';
    $result = $this->generateService->handleCondition($condition);
    $this->assertEquals(['fg&notinstalledyet||ca&installed', 'pl&charged'], $result);
});
test('test function handle condition when condition not string json', function () {
    $condition = '[{id:1,"apps":[{"name":"fg","status":"notinstalledyet"},{"name":"ca","status":"installed"}]},{"id":2,"apps":[{"name":"pl","status":"charged"}]}]';
    $result = $this->generateService->handleCondition($condition);
    $this->assertEquals([], $result);
});
test('test function handle condition when condition is empty array', function () {
    $condition = '[]';
    $result = $this->generateService->handleCondition($condition);
    $this->assertEquals([], $result);
});
//test show update
test('edit should return generate, discount data and deletion status', function () {
    $generateId = 1;
    $databaseNames = ['cs', 'pp'];
    $appName = 'cs';
    $discountId = 100;

    $generate = Generate::factory()->make([
        'id' => $generateId,
        'discount_id' => $discountId,
        'app_name' => $appName
    ]);

    $discount = Discount::factory()->make([
        'id' => $discountId,
    ]);

    $this->generateRepository->shouldReceive('findById')
        ->once()
        ->with($generateId)
        ->andReturn($generate);

    $this->couponRepository->shouldReceive('findByDiscountIdAndCode')
        ->once()
        ->with($discountId, $appName)
        ->andReturn(null);

    $discountsCs = [
        ['id' => 100, 'name' => 'CS Discount 1'],
        ['id' => 101, 'name' => 'CS Discount 2']
    ];

    $discountsPp = [
        ['id' => 200, 'name' => 'PP Discount 1'],
        ['id' => 201, 'name' => 'PP Discount 2']
    ];

    $this->discountRepository->shouldReceive('getAllDiscountIdAndName')
        ->once()
        ->with('cs')
        ->andReturn($discountsCs);

    $this->discountRepository->shouldReceive('getAllDiscountIdAndName')
        ->once()
        ->with('pp')
        ->andReturn($discountsPp);

    config(['database.connections.cs.app_name' => 'Currency Switcher']);
    config(['database.connections.pp.app_name' => 'Promotion Popup']);

    // Thực thi và kiểm tra kết quả
    $result = $this->generateService->edit($generateId, $databaseNames);

    expect($result)->toBeArray()
        ->toHaveKeys(['generate', 'discountData', 'status_del'])
        ->and($result['generate'])->toBe($generate)
        ->and($result['status_del'])->toBeTrue()
        ->and($result['discountData'])->toBeArray()->toHaveCount(4)
        ->and($result['discountData'][0])->toMatchArray([
            'id' => 100,
            'name' => 'CS Discount 1',
            'databaseName' => 'cs',
            'appName' => 'Currency Switcher'
        ])
        ->and($result['discountData'][2])->toMatchArray([
            'id' => 200,
            'name' => 'PP Discount 1',
            'databaseName' => 'pp',
            'appName' => 'Promotion Popup'
        ]);
});

////update generate
test('update generate fails when generate not found', function () {
    $generateIdNotExist = 1000000;
    $this->generateRepository->shouldReceive('findById')
        ->with($generateIdNotExist)
        ->andReturn(null);

    expect(fn () => $this->generateService->update($generateIdNotExist, []))->toThrow(NotFoundException::class, 'Generate not found');
});

test('update generate fails when no coupon has code like GENAUTO% and missing value required', function () {
    // Create app_name variable to avoid repetition
    $appName = 'cs';

    // Minimal discount object with only needed id
    $discount = Discount::factory()->make([
        'id' => 1,
    ]);

    // Minimal generate object with only needed properties
    $generate = Generate::factory()->make([
        'id' => 1,
        'app_name' => $appName,
        'discount_id' => $discount->id,
    ]);

    $this->generateRepository->shouldReceive('findById')
        ->with($generate->id)
        ->andReturn($generate);

    $this->couponRepository->shouldReceive('findByDiscountIdAndCode')
        ->with($discount->id, $appName)
        ->andReturn(null);

    $mockValidator = Mockery::mock('alias:App\Validator\GenerateUpdateValidator');
    $mockValidator->shouldReceive('validateUpdate')
        ->withArgs(function ($hasNoCoupon, $attributes) {
            return $hasNoCoupon === true && empty($attributes);
        })
        ->andThrow(GenerateException::validateUpdate([
            'expired_range' => ['The expired range field is required.'],
            'app_url' => ['The app url field is required.'],
            'discount_app' => ['The discount app field is required.']
        ]));

    expect(fn () => $this->generateService->update($generate->id, []))
        ->toThrow(function (GenerateException $exception) {
            expect($exception->getErrors()['expired_range'][0])->toBe('The expired range field is required.')
                ->and($exception->getErrors()['app_url'][0])->toBe('The app url field is required.')
                ->and($exception->getErrors()['discount_app'][0])->toBe('The discount app field is required.');
        });
});
test('update generate fails when no coupon has code like GENAUTO% and discount new not found', function () {
    // Create app_name variable to avoid repetition
    $appName = 'cs';
    $discountIdNew = 10000;

    $mockValidator = Mockery::mock('alias:App\Validator\GenerateUpdateValidator');
    $mockValidator->shouldReceive('validateUpdate')
        ->withAnyArgs()
        ->andReturnNull();

    // Minimal discount object
    $discount = Discount::factory()->make([
        'id' => 1,
    ]);

    // Minimal generate object
    $generate = Generate::factory()->make([
        'id' => 1,
        'app_name' => $appName,
        'discount_id' => $discount->id,
    ]);

    $this->generateRepository->shouldReceive('findById')
        ->with($generate->id)
        ->andReturn($generate);

    $this->couponRepository->shouldReceive('findByDiscountIdAndCode')
        ->with($discount->id, $appName)
        ->andReturn(null);

    $this->discountRepository->shouldReceive('findById')
        ->with($discountIdNew, $appName)
        ->andReturn(null);

    // Minimal data required for update
    $dataUpdateGenerate = [
        'discount_app' => $discountIdNew . '&' . $appName,
    ];

    expect(function () use ($dataUpdateGenerate, $generate) {
        $this->generateService->update($generate->id, $dataUpdateGenerate);
    })->toThrow(NotFoundException::class, 'Discount not found');
});
test('update generate fails when no coupon has code like GENAUTO% and discount new expired', function () {
    // Tạo biến cho app_name để tránh lặp lại
    $appName = 'cs';

    // Thiết lập mock validator
    $mockValidator = Mockery::mock('alias:App\Validator\GenerateUpdateValidator');
    $mockValidator->shouldReceive('validateUpdate')
        ->withAnyArgs()
        ->andReturnNull();

    // Chỉ giữ lại thuộc tính cần thiết
    $discount = Discount::factory()->make([
        'id' => 1,
    ]);

    $generate = Generate::factory()->make([
        'id' => 1,
        'app_name' => $appName,
        'discount_id' => $discount->id,
    ]);

    $discountIdNew = 10000;
    $discountNew = Discount::factory()->make([
        'id' => $discountIdNew,
        'expired_at' => now()->subDays(1), // Chỉ cần thuộc tính này để test hết hạn
    ]);

    // Cấu hình mock repository
    $this->generateRepository->shouldReceive('findById')
        ->with($generate->id)
        ->andReturn($generate);

    $this->couponRepository->shouldReceive('findByDiscountIdAndCode')
        ->with($discount->id, $appName)
        ->andReturn(null);

    $this->discountRepository->shouldReceive('findById')
        ->with($discountIdNew, $appName)
        ->andReturn($discountNew);

    // Dữ liệu update - chỉ cần thuộc tính discount_app
    $dataUpdateGenerate = [
        'discount_app' => $discountIdNew . '&' . $appName,
    ];

    // Kiểm tra exception
    expect(function () use ($dataUpdateGenerate, $generate) {
        $this->generateService->update($generate->id, $dataUpdateGenerate);
    })->toThrow(function (DiscountException $exception) {
        expect($exception->getErrors()['error'])->toBe('Discount expired');
    });
});
test('update generate fails when no coupon has code like GENAUTO% and generate exist discount_id and app_name', function () {
    // Tạo biến cho các giá trị lặp lại
    $appName = 'cs';
    $discountIdNew = 10000;

    // Tạo các đối tượng cần thiết với thuộc tính tối thiểu
    $discount = Discount::factory()->make([
        'id' => 1,
    ]);

    $discountNew = Discount::factory()->make([
        'id' => $discountIdNew,
        'expired_at' => now()->addDays(1),
    ]);

    $generate = Generate::factory()->make([
        'id' => 5,
        'app_name' => $appName,
        'discount_id' => $discount->id,
    ]);

    $generateExistDiscountIdAndAppName = Generate::factory()->make([
        'id' => 10,
        'app_name' => $appName,
        'discount_id' => $discountIdNew,
    ]);

    // Cấu hình repository mocks
    $this->generateRepository->shouldReceive('findById')
        ->with($generate->id)
        ->andReturn($generate);

    $this->couponRepository->shouldReceive('findByDiscountIdAndCode')
        ->with($discount->id, $appName)
        ->andReturn(null);

    $this->discountRepository->shouldReceive('findById')
        ->with($discountIdNew, $appName)
        ->andReturn($discountNew);

    // Cấu hình validator để vượt qua validation
    $mockValidator = Mockery::mock('alias:App\Validator\GenerateUpdateValidator');
    $mockValidator->shouldReceive('validateUpdate')
        ->withAnyArgs()
        ->andReturnNull();

    // Dữ liệu update đơn giản hóa
    $dataUpdateGenerate = [
        'discount_app' => $discountIdNew . '&' . $appName,
    ];

    $this->generateRepository->shouldReceive('findByDiscountIdAndAppName')
        ->with($discountIdNew, $appName)
        ->andReturn($generateExistDiscountIdAndAppName);

    // Kiểm tra exception
    expect(function () use ($dataUpdateGenerate, $generate) {
        $this->generateService->update($generate->id, $dataUpdateGenerate);
    })->toThrow(function (GenerateException $exception) {
        expect($exception->getErrors()['error'])->toBe('Generate existed discount_id and app_name');
    });
});

test('update generate fails when has coupon has code like GENAUTO% and missing value required', function () {
    // Tạo biến cho các giá trị lặp lại
    $appName = 'cs';

    // Tạo các đối tượng cần thiết với thuộc tính tối thiểu
    $discount = Discount::factory()->make([
        'id' => 1,
    ]);

    $coupon = Coupon::factory()->make([
        'discount_id' => $discount->id,
        'code' => 'GENAUTO100',
    ]);

    $generate = Generate::factory()->make([
        'id' => 1,
        'app_name' => $appName,
        'discount_id' => $discount->id,
    ]);

    // Cấu hình repository mocks
    $this->generateRepository->shouldReceive('findById')
        ->with($generate->id)
        ->andReturn($generate);

    $this->couponRepository->shouldReceive('findByDiscountIdAndCode')
        ->with($discount->id, $appName)
        ->andReturn($coupon);

    // Cấu hình validator mock
    $mockValidator = Mockery::mock('alias:App\Validator\GenerateUpdateValidator');
    $mockValidator->shouldReceive('validateUpdate')
        ->withArgs(function ($hasNoCoupon, $attributes) {
            return $hasNoCoupon === false && empty($attributes);
        })
        ->andThrow(GenerateException::validateUpdate([
            'expired_range' => ['The expired range field is required.'],
            'app_url' => ['The app url field is required.']
        ]));

    // Kiểm tra exception
    expect(function () use ($generate) {
        $this->generateService->update($generate->id, []);
    })->toThrow(function (GenerateException $exception) {
        expect($exception->getErrors()['expired_range'][0])->toBe('The expired range field is required.')
            ->and($exception->getErrors()['app_url'][0])->toBe('The app url field is required.');
    });
});
test('update generate fails when has coupon has code like GENAUTO% and trying to change discount_app', function () {
    // Tạo biến cho các giá trị lặp lại
    $appName = 'cs';
    $discountId = 1;

    // Đối tượng với thuộc tính tối thiểu
    $generate = Generate::factory()->make([
        'id' => 123,
        'app_name' => $appName,
        'discount_id' => $discountId,
    ]);

    $coupon = Coupon::factory()->make([
        'discount_id' => $discountId,
        'code' => 'GENAUTO1000',
    ]);

    // Cấu hình repository mocks
    $this->generateRepository->shouldReceive('findById')
        ->with($generate->id)
        ->andReturn($generate);

    $this->couponRepository->shouldReceive('findByDiscountIdAndCode')
        ->with($discountId, $appName)
        ->andReturn($coupon);

    // Tạo mock cho validator
    $mockValidator = Mockery::mock('alias:App\Validator\GenerateUpdateValidator');
    $mockValidator->shouldReceive('validateUpdate')
        ->withArgs(function ($hasNoCoupon, $attributes) {
            return $hasNoCoupon === false && isset($attributes['discount_app']);
        })
        ->andThrow(GenerateException::canNotUpdateDiscountIdAndAppName());

    // Dữ liệu update với discount_app
    $dataUpdate = [
        'discount_app' => $discountId . '&' . $appName,
        'expired_range' => 51,
        'app_url' => 'http://example.com',
    ];

    // Kiểm tra exception
    expect(fn () => $this->generateService->update($generate->id, $dataUpdate))
        ->toThrow(function (GenerateException $exception) {
            expect($exception->getErrors()['error'])->toBe('Can not update discount id and app name');
        });
});
test('update generate success when no coupon has code like GENAUTO%', function () {
    // Tạo biến cho các giá trị lặp lại
    $appName = 'cs';
    $discountId = 1;
    $generateId = 123;

    // Tạo các đối tượng với thuộc tính tối thiểu
    $generate = Generate::factory()->make([
        'id' => $generateId,
        'app_name' => $appName,
        'discount_id' => $discountId,
    ]);

    $discount = Discount::factory()->make([
        'id' => $discountId,
        'expired_at' => now()->addDays(1),
    ]);

    // Cấu hình repository mocks
    $this->generateRepository->shouldReceive('findById')
        ->with($generateId)
        ->andReturn($generate);

    $this->couponRepository->shouldReceive('findByDiscountIdAndCode')
        ->with($discountId, $appName)
        ->andReturn(null);

    $this->discountRepository->shouldReceive('findById')
        ->with($discountId, $appName)
        ->andReturn($discount);

    // Thiết lập mock cho validator
    $mockValidator = Mockery::mock('alias:App\Validator\GenerateUpdateValidator');
    $mockValidator->shouldReceive('validateUpdate')
        ->withAnyArgs()
        ->andReturnNull();

    // Dữ liệu đầu vào cho cập nhật
    $inputData = [
        'discount_app' => $discountId . '&' . $appName,
        'expired_range' => 51,
        'app_url' => 'http://example.com',
        'condition_object' => '[{"id":1,"apps":[{"name":"fg","status":"notinstalledyet"}]}]',
        'success_message' => 'success',
        'extend_message' => 'extend',
    ];

    // Dữ liệu đã xử lý dự kiến
    $processedData = [
        'expired_range' => 51,
        'app_url' => 'http://example.com',
        'conditions' => ['fg&notinstalledyet'],
        'success_message' => ['message' => 'success', 'extend' => 'extend'],
    ];

    $this->generateRepository->shouldReceive('updateGenerate')
        ->with($generateId, Mockery::subset($processedData))
        ->andReturn($processedData);

    // Gọi phương thức service
    $result = $this->generateService->update($generateId, $inputData);

    // Kiểm tra kết quả
    expect($result)->toBeArray()
        ->and($result['expired_range'])->toBe(51)
        ->and($result['conditions'])->toBe(['fg&notinstalledyet'])
        ->and($result['success_message'])->toBe(['message' => 'success', 'extend' => 'extend']);
});
test('update generate success when has coupon has code like GENAUTO%', function () {
    // Tạo biến cho các giá trị lặp lại
    $appName = 'cs';
    $discountId = 1;
    $generateId = 123;

    // Tạo các đối tượng với thuộc tính tối thiểu
    $discount = Discount::factory()->make([
        'id' => $discountId,
        'expired_at' => now()->addDays(1),
    ]);

    $coupon = Coupon::factory()->make([
        'discount_id' => $discountId,
        'code' => 'GENAUTO100',
    ]);

    $generate = Generate::factory()->make([
        'id' => $generateId,
        'app_name' => $appName,
        'discount_id' => $discountId,
    ]);

    // Cấu hình repository mocks
    $this->generateRepository->shouldReceive('findById')
        ->with($generateId)
        ->andReturn($generate);

    $this->couponRepository->shouldReceive('findByDiscountIdAndCode')
        ->with($discountId, $appName)
        ->andReturn($coupon);

    // Tạo mock cho validator
    $mockValidator = Mockery::mock('alias:App\Validator\GenerateUpdateValidator');
    $mockValidator->shouldReceive('validateUpdate')
        ->withAnyArgs()
        ->andReturnNull();

    // Dữ liệu đầu vào cho cập nhật
    $dataUpdateGenerate = [
        'expired_range' => 51,
        'app_url' => 'http://localhost:8000/admin/generates_new',
        'limit' => 5,
        'condition_object' => '[{"id":1,"apps":[{"name":"fg","status":"installedyet"}]},{"id":2,"apps":[{"name":"pp","status":"uninstalled"}]}]',
    ];

    // Dữ liệu đã xử lý dự kiến
    $processedData = [
        'expired_range' => 51,
        'app_url' => 'http://localhost:8000/admin/generates_new',
        'limit' => 5,
        'conditions' => ['fg&installedyet', 'pp&uninstalled'],
    ];

    $this->generateRepository->shouldReceive('updateGenerate')
        ->with($generateId, Mockery::subset($processedData))
        ->andReturn($processedData);

    // Gọi phương thức service và kiểm tra kết quả
    $result = $this->generateService->update($generateId, $dataUpdateGenerate);

    expect($result)->toBeArray()
        ->and($result['conditions'])->toBe(['fg&installedyet', 'pp&uninstalled'])
        ->and($result['expired_range'])->toBe(51)
        ->and($result['app_url'])->toBe('http://localhost:8000/admin/generates_new');
});

//test destroy
test('destroy generate coupon fails when generate not found', function () {
    // Use a descriptive variable name
    $nonExistentId = 1000000;

    $this->generateRepository->shouldReceive('findById')
        ->with($nonExistentId)
        ->andReturn(null);

    expect(fn () => $this->generateService->destroy($nonExistentId))
        ->toThrow(NotFoundException::class, 'Generate not found');
});
test('destroy generate coupon success', function () {
    // Define a consistent ID variable
    $generateId = 123;

    // Create minimal object with only needed properties
    $generate = Generate::factory()->make([
        'id' => $generateId,
    ]);

    // Use consistent repository method name
    $this->generateRepository->shouldReceive('findById')
        ->with($generateId)
        ->andReturn($generate);

    $this->generateRepository->shouldReceive('destroyGenerate')
        ->with($generateId)
        ->once();

    // Execute and verify no exceptions
    $this->generateService->destroy($generateId);

    // Pest automatically checks that no exceptions were thrown
});

//private generate coupon
test('private generate coupon fails when ip not supported', function () {
    $invalidIp = '0.0.0.1';
    $generateId = 1;
    $shopName = 'testShop';

    $result = $this->generateService->privateGenerateCoupon($invalidIp, $generateId, $shopName);

    expect($result)->toBeArray()
        ->and($result['status'])->toBeFalse()
        ->and($result['message'])->toBe('Ip not valid!');
});
test('private generate coupon fails when generate not found', function () {
    // Use descriptive variable names
    $validIp = '127.0.0.1';
    $generateIdNotExist = 100000;
    $shopName = 'testShop';

    $this->generateRepository->shouldReceive('findById')
        ->with($generateIdNotExist)
        ->andReturn(null);

    $result = $this->generateService->privateGenerateCoupon($validIp, $generateIdNotExist, $shopName);

    expect($result)->toBeArray()
        ->and($result['status'])->toBeFalse()
        ->and($result['message'])->toBe('Generate not exist!');
});
test('private generate coupon fails when coupon used', function () {
    $validIp = '127.0.0.1';
    $generateId = 123;
    $discountId = 456;
    $shopName = 'testShop';
    $shopDomain = $shopName . '.myshopify.com';
    $appName = 'cs';

    $generate = Generate::factory()->make([
        'id' => $generateId,
        'app_name' => $appName,
        'discount_id' => $discountId,
    ]);

    $coupon = Coupon::factory()->make([
        'id' => 789,
        'code' => 'GENAUTO123',
        'times_used' => 1, // Used coupon
    ]);

    $this->generateRepository->shouldReceive('findById')
        ->with($generateId)
        ->andReturn($generate);

    $this->couponRepository->shouldReceive('findByDiscountIdandShop')
        ->with($discountId, $shopDomain, $appName)
        ->andReturn($coupon);

    $result = $this->generateService->privateGenerateCoupon($validIp, $generateId, $shopName);

    expect($result)->toBeArray()
        ->and($result['status'])->toBeFalse()
        ->and($result['message'])->toBe('Coupon used!');
});
test('private generate coupon success when has coupon and coupon not used', function () {
    $validIp = '127.0.0.1';
    $shopName = 'testShop';
    $shopDomain = $shopName . '.myshopify.com';
    $appName = 'cs';
    $discountId= 456;

    $generate = Generate::factory()->make([
        'id' => 123,
        'app_name' => 'cs',
        'discount_id' => $discountId,
        'status' => true,
    ]);
    $coupon = Coupon::factory()->make([
        'id' => 789,
        'code' => 'GENAUTO123',
        'discount_id' => $discountId,
        'times_used' => 0, //coupon not used
    ]);

    $this->generateRepository->shouldReceive('findById')
        ->with($generate->id)
        ->andReturn($generate);

    $this->couponRepository->shouldReceive('findByDiscountIdandShop')
        ->with($generate->discount_id, $shopDomain, $appName)
        ->andReturn($coupon);

    $result = $this->generateService->privateGenerateCoupon($validIp, $generate->id, $shopName);

    expect($result)->toBe([
        'status' => true,
        'message' => 'Coupon created!',
    ]);
});
test('private generate coupon fails when discount not found', function () {
    $discountIdNotExist = 10000;
    $shopName = 'testShop';
    $shopDomain = $shopName . '.myshopify.com';
    $appName = 'cs';

    $generate = Generate::factory()->make([
        'id' => 123,
        'app_name' => $appName,
        'discount_id' => $discountIdNotExist,
        'status' => true,
    ]);
    $this->generateRepository->shouldReceive('findById')
        ->with($generate->id)
        ->andReturn($generate);

    $this->couponRepository->shouldReceive('findByDiscountIdandShop')
        ->with($generate->discount_id, $shopDomain, $appName)  // Sửa lại tham số đúng
        ->andReturn(null);

    $this->discountRepository->shouldReceive('findById')
        ->with($generate->discount_id, $generate->app_name)
        ->andReturn(null);

    $result = $this->generateService->privateGenerateCoupon('127.0.0.1', $generate->id, 'shopName');
    expect($result)->toBe([
        'status' => false,
        'message' => 'Discount not found!',
    ]);
});
test('private generate coupon fails when discount expired', function () {
    $invalidIp = '0.0.0.1';
    $shopName = 'testShop';
    $shopDomain = $shopName . '.myshopify.com';
    $appName = 'cs';
    $discount = Discount::factory()->make([
        'id' => 456,
        'expired_at' => now()->subDay(1),//expired
    ]);
    $generate = Generate::factory()->make([
        'id' => 123,
        'app_name' => $appName,
        'discount_id' => $discount->id,
        'status' => true,
    ]);
    $this->generateRepository->shouldReceive('findById')
        ->with($generate->id)
        ->andReturn($generate);

    $this->couponRepository->shouldReceive('findByDiscountIdandShop')
        ->with($generate->discount_id, $shopDomain, $appName)
        ->andReturn(null);

    $this->discountRepository->shouldReceive('findById')
        ->with($generate->discount_id, $generate->app_name)
        ->andReturn($discount);
    $result = $this->generateService->privateGenerateCoupon($invalidIp, $generate->id, $shopName);
    expect($result)->toBe([
        'status' => false,
        'message' => 'Discount expired!',
    ]);
});
test('private generate coupon fails when limit is reached', function () {
    $invalidIp = '0.0.0.1';
    $shopName = 'testShop';
    $shopDomain = $shopName . '.myshopify.com';
    $appName = 'cs';

    $discount = Discount::factory()->make([
        'id' => 456,
        'expired_at' => now()->addDay(),
    ]);
    $generate = Generate::factory()->make([
        'id' => 123,
        'app_name' => $appName,
        'discount_id' => $discount->id,
        'status' => true,
        'limit' => 1, //limit coupon
    ]);
    $this->generateRepository->shouldReceive('findById')
        ->with($generate->id)
        ->andReturn($generate);
    $this->couponRepository->shouldReceive('findByDiscountIdandShop')
        ->with($generate->discount_id, $shopDomain,$appName)
        ->andReturn(null);
    $this->discountRepository->shouldReceive('findById')
        ->with($generate->discount_id, $generate->app_name)
        ->andReturn($discount);

    $this->couponRepository->shouldReceive('countByDiscountIdAndCode')
        ->with($generate->discount_id, 'cs')
        ->andReturn(1);

    $result = $this->generateService->privateGenerateCoupon($invalidIp, $generate->id, $shopName);

    expect($result)->toBe([
        'status' => false,
        'message' => 'Limit Coupon!',
    ]);
});
test('private generate coupon success when no code for shop and create new code', function () {
    $validIp = '127.0.0.1';
    $generateId = 123;
    $discountId = 456;
    $shopName = 'testShop';
    $shopDomain = $shopName . '.myshopify.com';
    $appName = 'cs';

    $generate = Generate::factory()->make([
        'id' => $generateId,
        'app_name' => $appName,
        'discount_id' => $discountId,
        'status' => true,
        'limit' => 10,
    ]);
    $discount = Discount::factory()->make([
        'id' => $discountId,
        'expired_at' => now()->addDay(),
    ]);

    $this->generateRepository->shouldReceive('findById')
        ->with($generateId)
        ->andReturn($generate);

    $this->couponRepository->shouldReceive('findByDiscountIdandShop')
        ->with($discountId, $shopDomain, $appName)
        ->andReturn(null);

    $this->discountRepository->shouldReceive('findById')
        ->with($discountId, $appName)
        ->andReturn($discount);

    $this->couponRepository->shouldReceive('countByDiscountIdAndCode')
        ->with($discountId, $appName)
        ->andReturn(0);

    $this->couponRepository->shouldReceive('findByCode')
        ->andReturn(null);

    $newCoupon = (object)[
        'code' => 'GENAUTO123',
    ];
    $this->couponRepository->shouldReceive('createCoupon')
        ->andReturn($newCoupon);

    $result = $this->generateService->privateGenerateCoupon($validIp, $generateId, $shopName);

    expect($result)->toBe([
        'status' => true,
        'message' => 'Success generate coupon!',
    ]);
});


//test generate coupon
test('generate coupon fails when generate not found', function () {
    $generateId = 9999;
    $timestamp = time();
    $shopId = 'shop_123';

    $this->generateRepository->shouldReceive('getGenerateById')
        ->with($generateId)
        ->andReturn(null);

    $result = $this->generateService->generateCoupon($generateId, $timestamp, $shopId);

    expect($result)
        ->toHaveKey('header_message')
        ->toHaveKey('content_message')
        ->toHaveKey('reasons')
        ->and($result['content_message'])->toBe('WHOOPS!')
        ->and($result['reasons'])->toBe('This offer does not exist!');
});
test('generate coupon fails when generate is inactive', function () {
    $generateId = 123;
    $timestamp = time();
    $shopId = 'shop_123';


    $generate = Generate::factory()->make([
        'id' => $generateId,
        'status' => false, //inactive
        'app_url' => 'https://example.com',
    ]);

    $this->generateRepository->shouldReceive('findById')
        ->with($generateId)
        ->andReturn($generate);

    $result = $this->generateService->generateCoupon($generateId, $timestamp, $shopId);

    expect($result)
        ->toHaveKey('content_message')
        ->toHaveKey('reasons')
        ->toHaveKey('app_url')
        ->toHaveKey('generate_id')
        ->and($result['content_message'])->toBe('WHOOPS!')
        ->and($result['reasons'])->toBe('This offer was disabled!')
        ->and($result['app_url'])->toBe('https://example.com')
        ->and($result['generate_id'])->toBe($generateId);
});

test('generate coupon fails when unable to connect the shop', function () {
    $generate =Generate::factory()->make([
        'id' => 1,
        'app_url' => 'http://example.com',
        'status' => true,
        'app_name' => 'test_app',
        'discount_id' => 123,
        'header_message' => null,
        'used_message' => null,
    ]);

    $this->generateRepository
        ->shouldReceive('findById')
        ->with($generate->id)
        ->once()
        ->andReturn($generate);

    $clientMock = Mockery::mock('overload:Customerio\Client');

    $clientMock->shouldReceive('__construct')
        ->withAnyArgs()
        ->once();

    $clientMock->shouldReceive('setAppAPIKey')
        ->withAnyArgs()
        ->once();

    $exception = new RequestException(
        'Error connecting to server',
        new \GuzzleHttp\Psr7\Request('GET', '/customers/shop123/attributes')
    );
    $shopId='123';

    $clientMock->shouldReceive('get')
        ->with('/customers/' . $shopId . '/attributes')
        ->once()
        ->andThrow($exception);

    $result = $this->generateService->generateCoupon($generate->id, time(), $shopId);

    expect($result)->toBeArray()
        ->and($result['headerMessage'])->toBe('Connection Error!')
        ->and($result['contentMessage'])->toBe('Oops! Can not connect to shop!')
        ->and($result['reasons'])->toBe('The shop may be down or experiencing issues. Please try again!')
        ->and($result['appUrl'])->toBe('http://example.com')
        ->and($result['generateId'])->toBe(1);
});
test('generate coupon fails when shop has coupon and coupon already used', function () {
    $generateId = 123;
    $timestamp = time();
    $shopId = 'shop_123';
    $shopName = 'shopname';
    $shopDomain = $shopName . '.myshopify.com';
    $appName = 'cs';
    $discountId = 456;

    $generate = Generate::factory()->make([
        'id' => $generateId,
        'status' => true,
        'app_url' => 'https://example.com',
        'app_name' => $appName,
        'discount_id' => $discountId,
        'header_message' => 'Custom Header',
        'used_message' => 'Already Used Message'
    ]);

    $coupon = (object)[
        'shop' => $shopDomain, //shop has coupon
        'code' => 'EXISTING123',
        'times_used' => 1 //coupon used
    ];
    $this->generateRepository->shouldReceive('findById')
        ->with($generateId)
        ->andReturn($generate);

    $clientMock = Mockery::mock('overload:Customerio\Client');

    $clientMock->shouldReceive('__construct')
        ->withArgs(['apiKey', 'siteId'])
        ->once();

    $clientMock->shouldReceive('setAppAPIKey')
        ->with('appKey')
        ->once();

    $clientMock->shouldReceive('get')
        ->with('/customers/' . $shopId . '/attributes')
        ->once()
        ->andReturn((object) ['customer' => (object) ['attributes' => (object) ['shop_name' => $shopName]]]);

    $this->couponRepository->shouldReceive('findByDiscountIdandShop')
        ->with($discountId, $shopDomain, $appName)
        ->andReturn($coupon);

    $result = $this->generateService->generateCoupon($generateId, $timestamp, $shopId);

    expect($result)
        ->toHaveKey('headerMessage')
        ->toHaveKey('contentMessage')
        ->toHaveKey('reasons')
        ->and($result['headerMessage'])->toBe('Custom Header');
});

test('generate coupon success when shop has coupon and coupon no used', function () {
    $generateId = 123;
    $timestamp = time();
    $shopId = 'shop_123';
    $shopName = 'shopname';
    $shopDomain = $shopName . '.myshopify.com';
    $appName = 'cs';
    $discountId = 456;

    $generate = Generate::factory()->make([
        'id' => $generateId,
        'status' => true,
        'app_url' => 'https://example.com',
        'app_name' => $appName,
        'discount_id' => $discountId,
        'header_message' => 'Custom Header',
        'used_message' => 'Already Used Message'
    ]);

    $coupon = (object)[
        'shop' => $shopDomain, //shop has coupon
        'code' => 'EXISTING123',
        'times_used' => 0 //coupon used
    ];
    $this->generateRepository->shouldReceive('findById')
        ->with($generateId)
        ->andReturn($generate);

    $clientMock = Mockery::mock('overload:Customerio\Client');

    $clientMock->shouldReceive('__construct')
        ->withArgs(['apiKey', 'siteId'])
        ->once();

    $clientMock->shouldReceive('setAppAPIKey')
        ->with('appKey')
        ->once();

    $clientMock->shouldReceive('get')
        ->with('/customers/' . $shopId . '/attributes')
        ->once()
        ->andReturn((object) ['customer' => (object) ['attributes' => (object) ['shop_name' => $shopName]]]);

    $this->couponRepository->shouldReceive('findByDiscountIdandShop')
        ->with($discountId, $shopDomain, $appName)
        ->andReturn($coupon);

    $result = $this->generateService->generateCoupon($generateId, $timestamp, $shopId);

    expect($result)
        ->toHaveKey('headerMessage')
        ->toHaveKey('contentMessage')
        ->toHaveKey('extendMessage')
        ->and($result['headerMessage'])->toBe('Custom Header');
});
test('generate coupon fails when shop does not have coupon and discount not found', function () {
    $generateId = 123;
    $timestamp = time();
    $shopId = 'shop_123';
    $shopName = 'shopname';
    $shopDomain = $shopName . '.myshopify.com';
    $appName = 'cs';
    $discountId = 456;

    $generate = Generate::factory()->make([
        'id' => $generateId,
        'status' => true,
        'app_url' => 'https://example.com',
        'app_name' => $appName,
        'discount_id' => $discountId,
        'header_message' => 'Custom Header',
    ]);

    $this->generateRepository->shouldReceive('findById')
        ->with($generateId)
        ->andReturn($generate);

    $clientMock = Mockery::mock('overload:Customerio\Client');

    $clientMock->shouldReceive('__construct')
        ->withAnyArgs()
        ->once();

    $clientMock->shouldReceive('setAppAPIKey')
        ->withAnyArgs()
        ->once();

    $clientMock->shouldReceive('get')
        ->with('/customers/' . $shopId . '/attributes')
        ->once()
        ->andReturn((object) ['customer' => (object) ['attributes' => (object) ['shop_name' => $shopName]]]);

    $this->couponRepository->shouldReceive('findByDiscountIdandShop')
        ->with($discountId, $shopDomain, $appName)
        ->andReturn(null);

    $this->findDiscountByIdNoCoupon = $this->discountRepository->shouldReceive('findById')
        ->with($discountId, $appName)
        ->andReturn(null);

    expect(fn() => $this->generateService->generateCoupon($generateId, $timestamp, $shopId))
        ->toThrow(NotFoundException::class, 'Discount not found!');
});

test('generate coupon fails when discount has expired', function () {
    $generateId = 123;
    $timestamp = time();
    $shopId = 'shop_123';
    $shopName = 'shopname';
    $shopDomain = $shopName . '.myshopify.com';
    $appName = 'cs';
    $discountId = 456;

    $generate = Generate::factory()->make([
        'id' => $generateId,
        'status' => true,
        'app_url' => 'https://example.com',
        'app_name' => $appName,
        'discount_id' => $discountId,
        'header_message' => 'Custom Header',
        'fail_message' => [
            'message' => 'Fail Message',
            'reason_expired' => 'Sorry, this offer has expired'
        ]
    ]);

    $discount = (object)[
        'id' => $discountId,
        'expired_at' => now()->subDay(), // Discount đã hết hạn
    ];

    $this->generateRepository->shouldReceive('findById')
        ->with($generateId)
        ->andReturn($generate);

    $clientMock = Mockery::mock('overload:Customerio\Client');

    $clientMock->shouldReceive('__construct')
        ->withAnyArgs()
        ->once();

    $clientMock->shouldReceive('setAppAPIKey')
        ->withAnyArgs()
        ->once();

    $clientMock->shouldReceive('get')
        ->with('/customers/' . $shopId . '/attributes')
        ->once()
        ->andReturn((object) ['customer' => (object) ['attributes' => (object) ['shop_name' => $shopName]]]);

    $this->couponRepository->shouldReceive('findByDiscountIdandShop')
        ->with($discountId, $shopDomain, $appName)
        ->andReturn(null);

    $this->discountRepository->shouldReceive('findById')
        ->with($discountId, $appName)
        ->andReturn($discount);

    $result = $this->generateService->generateCoupon($generateId, $timestamp, $shopId);

    expect($result)
        ->toMatchArray([
            'headerMessage' => 'Custom Header',
            'contentMessage' => 'Fail Message',
            'reasons' => 'Sorry, this offer has expired'
        ]);
});
test('generate coupon fails when coupon limit reached', function () {
    $generateId = 123;
    $timestamp = time();
    $shopId = 'shop_123';
    $shopName = 'shopname';
    $shopDomain = $shopName . '.myshopify.com';
    $appName = 'cs';
    $discountId = 456;

    $generate = Generate::factory()->make([
        'id' => $generateId,
        'status' => true,
        'app_url' => 'https://example.com',
        'app_name' => $appName,
        'discount_id' => $discountId,
        'header_message' => 'Custom Header',
        'limit' => 5, // Giới hạn số lượng coupon
        'fail_message' => [
            'message' => 'Fail Message',
            'reason_limit' => 'Coupon limit reached'
        ]
    ]);

    $discount = (object)[
        'id' => $discountId,
        'expired_at' => now()->addDay(), // Chưa hết hạn
    ];

    $this->generateRepository->shouldReceive('findById')
        ->with($generateId)
        ->andReturn($generate);

    $clientMock = Mockery::mock('overload:Customerio\Client');

    $clientMock->shouldReceive('__construct')
        ->withAnyArgs()
        ->once();

    $clientMock->shouldReceive('setAppAPIKey')
        ->withAnyArgs()
        ->once();

    $clientMock->shouldReceive('get')
        ->with('/customers/' . $shopId . '/attributes')
        ->once()
        ->andReturn((object) ['customer' => (object) ['attributes' => (object) ['shop_name' => $shopName]]]);

    $this->couponRepository->shouldReceive('findByDiscountIdandShop')
        ->with($discountId, $shopDomain, $appName)
        ->andReturn(null);

    $this->discountRepository->shouldReceive('findById')
        ->with($discountId, $appName)
        ->andReturn($discount);

    $this->couponRepository->shouldReceive('countCouponByDiscountIdAndCode')
        ->with($discountId, $appName)
        ->andReturn(5); // Đã đạt đến giới hạn

    $result = $this->generateService->generateCoupon($generateId, $timestamp, $shopId);

    expect($result)
        ->toHaveKey('headerMessage')
        ->toHaveKey('contentMessage')
        ->toHaveKey('reasons')
        ->and($result['headerMessage'])->toBe('Custom Header')
        ->and($result['contentMessage'])->toBe('Fail Message')
        ->and($result['reasons'])->toBe('Coupon limit reached');
});




test('generate coupon returns existing code when coupon already exists and not used', function () {
    $generateId = 123;
    $timestamp = time();
    $shopId = 'shop_123';
    $shopName = 'shopname'; // Hard-coded trong generateCoupon
    $shopDomain = $shopName . '.myshopify.com';
    $appName = 'cs';
    $discountId = 456;

    // Tạo generate hợp lệ
    $generate = Generate::factory()->make([
        'id' => $generateId,
        'status' => true,
        'app_url' => 'https://example.com',
        'app_name' => $appName,
        'discount_id' => $discountId,
        'header_message' => 'Custom Header',
        'success_message' => [
            'message' => 'Success content',
            'extend' => 'Extended info'
        ]
    ]);

    // Tạo coupon đã tồn tại nhưng chưa dùng
    $coupon = (object)[
        'code' => 'EXISTING123',
        'times_used' => 0
    ];

    // Mock các phương thức
    $this->generateRepository->shouldReceive('getGenerateById')
        ->with($generateId)
        ->andReturn($generate);

    $this->couponRepository->shouldReceive('findByDiscountIdandShop')
        ->with($discountId, $shopDomain, $appName)
        ->andReturn($coupon);

    $result = $this->generateService->generateCoupon($generateId, $timestamp, $shopId);

    expect($result)
        ->toHaveKey('header_message')
        ->toHaveKey('content_message')
        ->toHaveKey('extend_message')
        ->toHaveKey('coupon_code')
        ->and($result['header_message'])->toBe('Custom Header')
        ->and($result['content_message'])->toBe('Success content')
        ->and($result['extend_message'])->toBe('Extended info')
        ->and($result['coupon_code'])->toBe('EXISTING123');
});



test('generate coupon fails when discount is expired', function () {
    $generateId = 123;
    $timestamp = time();
    $shopId = 'shop_123';
    $shopName = 'shopname'; // Hard-coded trong generateCoupon
    $shopDomain = $shopName . '.myshopify.com';
    $appName = 'cs';
    $discountId = 456;

    // Tạo generate hợp lệ với expired_range
    $generate = Generate::factory()->make([
        'id' => $generateId,
        'status' => true,
        'app_url' => 'https://example.com',
        'app_name' => $appName,
        'discount_id' => $discountId,
        'expired_range' => 7,
        'fail_message' => [
            'message' => 'Fail content',
            'reason_expired' => 'Custom expired reason'
        ]
    ]);

    // Tạo discount đã hết hạn
    $discount = Discount::factory()->make([
        'id' => $discountId,
        'expired_at' => now()->subDay(),
    ]);

    // Mock các phương thức
    $this->generateRepository->shouldReceive('getGenerateById')
        ->with($generateId)
        ->andReturn($generate);

    $this->couponRepository->shouldReceive('findByDiscountIdandShop')
        ->with($discountId, $shopDomain, $appName)
        ->andReturn(null);

    $this->discountRepository->shouldReceive('findById')
        ->with($discountId, $appName)
        ->andReturn($discount);

    $result = $this->generateService->generateCoupon($generateId, $timestamp, $shopId);

    expect($result)
        ->toHaveKey('content_message')
        ->toHaveKey('reasons')
        ->and($result['content_message'])->toBe('Fail content')
        ->and($result['reasons'])->toBe('Custom expired reason');
});

test('generate coupon fails when limit reached', function () {
    $generateId = 123;
    $timestamp = time();
    $shopId = 'shop_123';
    $shopName = 'shopname'; // Hard-coded trong generateCoupon
    $shopDomain = $shopName . '.myshopify.com';
    $appName = 'cs';
    $discountId = 456;

    // Tạo generate hợp lệ với limit
    $generate = Generate::factory()->make([
        'id' => $generateId,
        'status' => true,
        'app_url' => 'https://example.com',
        'app_name' => $appName,
        'discount_id' => $discountId,
        'limit' => 5,
        'fail_message' => [
            'message' => 'Fail content',
            'reason_limit' => 'Custom limit reason'
        ]
    ]);

    // Tạo discount còn hạn
    $discount = Discount::factory()->make([
        'id' => $discountId,
        'expired_at' => now()->addDay(),
    ]);

    // Mock các phương thức
    $this->generateRepository->shouldReceive('getGenerateById')
        ->with($generateId)
        ->andReturn($generate);

    $this->couponRepository->shouldReceive('findByDiscountIdandShop')
        ->with($discountId, $shopDomain, $appName)
        ->andReturn(null);

    $this->discountRepository->shouldReceive('findById')
        ->with($discountId, $appName)
        ->andReturn($discount);

    // Trả về số lượng coupon bằng limit
    $this->couponRepository->shouldReceive('countCouponByDiscountIdAndCode')
        ->with($discountId, $appName)
        ->andReturn(5);

    $result = $this->generateService->generateCoupon($generateId, $timestamp, $shopId);

    expect($result)
        ->toHaveKey('content_message')
        ->toHaveKey('reasons')
        ->and($result['content_message'])->toBe('Fail content')
        ->and($result['reasons'])->toBe('Custom limit reason');
});




//test('generate coupon creates new coupon successfully', function () {
//    $generateId = 123;
//    $timestamp = time();
//    $shopId = 'shop_123';
//    $shopName = 'shopname'; // Hard-coded trong generateCoupon
//    $shopDomain = $shopName . '.myshopify.com';
//    $appName = 'cs';
//    $discountId = 456;
//    $couponCode = 'GENAUTO123';
//
//    // Tạo generate hợp lệ
//    $generate = Generate::factory()->make([
//        'id' => $generateId,
//        'status' => true,
//        'app_url' => 'https://example.com',
//        'app_name' => $appName,
//        'discount_id' => $discountId,
//        'header_message' => 'Custom Header',
//        'success_message' => [
//            'message' => 'Success content',
//            'extend' => 'Extended info'
//        ],
//        'conditions' => [] // Thêm conditions rỗng để tránh lỗi
//    ]);
//
//    // Tạo discount còn hạn
//    $discount = Discount::factory()->make([
//        'id' => $discountId,
//        'expired_at' => now()->addDay(),
//    ]);
//
//    // Tạo coupon mới
//    $newCoupon = (object)[
//        'code' => $couponCode,
//        'times_used' => 0
//    ];
//
//    // Tạo một subclass của GenerateServiceImp để ghi đè phương thức private
//    $testService = new class($this->generateRepository, $this->discountRepository, $this->couponRepository) extends \App\Services\Generate\GenerateServiceImp {
//        // Ghi đè phương thức protected/private để trả về shop attributes
//        public function getShopAttributes($shopId) {
//            return ['some_attribute' => 'value']; // Trả về attributes giả lập
//        }
//
//        // Nếu cần, ghi đè phương thức checkConditions để không cần attributes
//        public function checkConditions($conditions, $attributes) {
//            return false; // Không có điều kiện nào không đạt
//        }
//    };
//
//    // Mock các phương thức
//    $this->generateRepository->shouldReceive('getGenerateById')
//        ->with($generateId)
//        ->andReturn($generate);
//
//    $this->couponRepository->shouldReceive('findByDiscountIdandShop')
//        ->with($discountId, $shopDomain, $appName)
//        ->andReturn(null);
//
//    $this->discountRepository->shouldReceive('findById')
//        ->with($discountId, $appName)
//        ->andReturn($discount);
//
//    $this->couponRepository->shouldReceive('countCouponByDiscountIdAndCode')
//        ->with($discountId, $appName)
//        ->andReturn(0);
//
//    // Mock findByCode để kiểm tra code tồn tại
//    $this->couponRepository->shouldReceive('findByCode')
//        ->andReturn(null);
//
//    // Mock createCoupon để tạo coupon mới
//    $this->couponRepository->shouldReceive('createCoupon')
//        ->andReturn($newCoupon);
//
//    // Sử dụng service được ghi đè thay vì service gốc
//    $result = $testService->generateCoupon($generateId, $timestamp, $shopId);
//
//    expect($result)
//        ->toHaveKey('header_message')
//        ->toHaveKey('content_message')
//        ->toHaveKey('extend_message')
//        ->toHaveKey('coupon_code')
//        ->and($result['header_message'])->toBe('Custom Header')
//        ->and($result['content_message'])->toBe('Success content')
//        ->and($result['extend_message'])->toBe('Extended info')
//        ->and($result['coupon_code'])->toBe($couponCode);
//});


//test partner


test('createCouponFromAffiliatePartner returns error when app code is invalid', function () {
    // Arrangement
    $formData = [
        'percentage' => 10,
        'trial_days' => 14
    ];
    $invalidAppCode = 'invalid_app';
    $shopName = 'test-shop';

    // Action
    $result = $this->generateService->createCouponFromAffiliatePartner($formData, $invalidAppCode, $shopName);

    // Assertion
    expect($result)
        ->toBeArray()
        ->toHaveKey('message')
        ->and($result['message'])->toBe('Not found connection');
});

test('createCouponFromAffiliatePartner fails when coupon already exists and not used', function () {

    $formData = [
        'percentage' => 10,
        'trial_days' => 14
    ];
    $appCode = 'up_promote';
    $shopName = 'test-shop';
    $connection = 'app_13';
    putenv('DB_CONNECTION_APP_13=' . $connection);
    $discountId = 123;
    $shopDomain = 'test-shop.myshopify.com';



    $discount = (object)[
        'id' => $discountId
    ];

    $existingCoupon = (object)[
        'id' => 456,
        'code' => 'AF-12345',
        'times_used' => 0
    ];

    $this->discountRepository->shouldReceive('UpdateOrCreateDiscountInAffiliatePartner')
        ->with($connection, Mockery::type('array'))
        ->andReturn($discount);

    $this->couponRepository->shouldReceive('getCouponByDiscountIdandShop')
        ->with($discount, $shopDomain, $connection)
        ->andReturn($existingCoupon);

    $result = $this->generateService->createCouponFromAffiliatePartner($formData, $appCode, $shopName);
    expect($result)
        ->toBeArray()
        ->toHaveKey('message')
        ->and($result['message'])->toBe('Coupon already exists');
});
test('createCouponFromAffiliatePartner creates new coupon when existing coupon has been used', function () {

    $formData = [
        'percentage' => 20,
        'trial_days' => 7
    ];
    $appCode = 'up_promote';
    $shopName = 'test-shop';
    $connection = 'app_13';
    putenv('DB_CONNECTION_APP_13=' . $connection);
    $discountId = 789;
    $shopDomain = 'test-shop.myshopify.com';

    $discount = (object)[
        'id' => $discountId
    ];

    $existingCoupon = (object)[
        'id' => 456,
        'code' => 'AF-12345',
        'times_used' => 1  // Coupon đã được sử dụng
    ];

    $newCoupon = (object)[
        'id' => 999,
        'code' => 'AF-xyz789',
        'discount_id' => $discountId,
        'shop' => $shopDomain,
        'times_used' => 0,
        'status' => 1,
        'automatic' => true
    ];

    $this->discountRepository->shouldReceive('UpdateOrCreateDiscountInAffiliatePartner')
        ->with($connection, Mockery::type('array'))
        ->andReturn($discount);

    $this->couponRepository->shouldReceive('findByDiscountIdandShop')
        ->with($discount->id, $shopDomain, $connection)
        ->andReturn($existingCoupon);

    $this->couponRepository->shouldReceive('findByCode')
        ->with(Mockery::any(),$connection)
        ->andReturn(null);
    $this->couponRepository->shouldReceive('createCoupon')
        ->with($connection, Mockery::type('array'))
        ->andReturn($newCoupon);

    $result = $this->generateService->createCouponFromAffiliatePartner($formData, $appCode, $shopName);
    expect($result)
        ->toBeArray()
        ->toHaveKey('message')
        ->toHaveKey('coupon')
        ->and($result['message'])->toBe('Coupon created successfully')
        ->and($result['coupon'])->toBe($newCoupon);
});



