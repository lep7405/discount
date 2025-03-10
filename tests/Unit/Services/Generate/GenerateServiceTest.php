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
use App\Services\Generate\GenerateServiceImp;
use Carbon\Carbon;
use Mockery;
uses(\Tests\TestCase::class);

beforeEach(function () {
    $this->generateRepository = Mockery::mock(GenerateRepository::class);
    $this->discountRepository = Mockery::mock(DiscountRepository::class);
    $this->couponRepository = Mockery::mock(CouponRepository::class);
    //2  cách , cách nào cũng được
//    $this->generateService = app()->instance(GenerateService::class, new GenerateServiceImp( $this->generateRepository,$this->discountRepository,$this->couponRepository));

    //cách viết này là cách viết ngắn gọn hơn , do cái container service nó dùng cái binddings để nó tìm nạp vào
//    $this->generateService = app()->make(GenerateService::class);
    $this->generateService = app()->make(GenerateService::class, [
        'generateRepository' => $this->generateRepository,
        'discountRepository' => $this->discountRepository,
        'couponRepository' => $this->couponRepository,
    ]);
    Coupon::on('cs')->delete();
    Discount::on('cs')->delete();
    Generate::query()->delete();
});

test('create generate fails when discount not found', function () {
    $this->discountRepository->shouldReceive('findDiscountByIdNoCoupon')
        ->with(1000000000, 'cs')
        ->andReturn(null);

    $data = [
        'discount_app' => '1000000000&cs',
    ];

    expect(function () use ($data) {
        $this->generateService->create($data);
    })->toThrow(NotFoundException::class, 'Discount not found');
});
test('create generate fails when discount expired', function () {
    $discount=(object)[
        'id'=>1,
        'name'=>'discount',
        'type'=>'percentage',
        'expired_at' => Carbon::now()->subDays(1),
    ];
    $this->discountRepository->shouldReceive('findDiscountByIdNoCoupon')
        ->with( $discount->id, 'cs')
        ->andReturn($discount);
    expect(function () use ($discount) {
        $this->generateService->create([
            'discount_app' => $discount->id . '&cs',
            'expired_range' => 50,
            'app_url' => 'http://localhost:8000/admin',
        ]);
    })->toThrow(function (DiscountException $e) {
        expect($e->getErrors()['error'][0])->toBe('Discount expired');
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
    $this->discountRepository->shouldReceive('findDiscountByIdNoCoupon')
        ->with($discount->id, 'cs')
        ->andReturn($discount);
    $this->generateRepository->shouldReceive('getGenerateByDiscountIdAndAppName')
        ->with($discount->id, 'cs')
        ->andReturn($generate);
    $data = [
        'discount_app' => $discount->id . '&cs',
        'expired_range' => 50,
        'app_url' => 'http://localhost:8000/admin',
    ];
    expect(function () use ($data) {
        $this->generateService->create($data);
    })->toThrow(function (GenerateException $e) {
        expect($e->getErrors()['error'][0])->toBe('Generate existed discount_id and app_name');
    });
});

test('test function handle condition', function () {
    $condition='[{"id":1,"apps":[{"name":"fg","status":"notinstalledyet"},{"name":"ca","status":"installed"}]},{"id":2,"apps":[{"name":"pl","status":"charged"}]}]';
    $result=$this->generateService->handleCondition($condition);
    $this->assertEquals(["fg&notinstalledyet||ca&installed","pl&charged"],$result);
});
test('test function handle condition when condition not string json', function () {
    $condition='[{id:1,"apps":[{"name":"fg","status":"notinstalledyet"},{"name":"ca","status":"installed"}]},{"id":2,"apps":[{"name":"pl","status":"charged"}]}]';
    $result=$this->generateService->handleCondition($condition);
    $this->assertEquals([],$result);
});

test('create generate success',function (){
    $discount=Discount::factory()->make([
        'id'=>1,
        'name'>'discount',
        'type'=>'percentage',
        'expired_at' => Carbon::now()->addDays(1),
    ]);

    $this->discountRepository->shouldReceive('findDiscountByIdNoCoupon')
        ->with($discount->id, 'cs')
        ->andReturn($discount);
    $this->generateRepository->shouldReceive('getGenerateByDiscountIdAndAppName')
        ->with($discount->id, 'cs')
        ->andReturn(null);

    $data = [
        'discount_app' => $discount->id . '&cs',
        'expired_range' => 50,
        'app_url' => 'http://localhost:8000/admin/generates_new', // Giữ nguyên vì là URL cố định
        'limit' => 5,
        'condition_object' => '[{"id":1,"apps":[{"name":"fg","status":"notinstalledyet"}]},{"id":2,"apps":[{"name":"pp","status":"uninstalled"}]}]',
        'header_message' => 'header',
        'success_message' => 'success',
        'used_message' => 'used',
        'fail_message' => 'fails',
        'extend_message' => 'extend',
        'reason_expired' => 'time',
        'reason_limit' => 'limit',
        'reason_condition' => 'notMatch'
    ];
    $dataCreateGenerate=[
        'discount_app' => $discount->id . '&cs',
        'expired_range' => 50,
        'app_url' => 'http://localhost:8000/admin/generates_new',
        'limit' => 5,
        'condition_object' => '[{"id":1,"apps":[{"name":"fg","status":"notinstalledyet"}]},{"id":2,"apps":[{"name":"pp","status":"uninstalled"}]}]',
        'header_message' => 'header',
        'success_message' => [
            'message' => 'success',
            'extend' => 'extend'
        ],
        'used_message' => 'used',
        'fail_message' => [
            'message' => 'fails',
            'reason_expired' => 'time',
            'reason_limit' => 'limit',
            'reason_condition' => 'notMatch'
        ],
        'extend_message' => 'extend',
        'reason_expired' => 'time',
        'reason_limit' => 'limit',
        'reason_condition' => 'notMatch',
        'app_name' => 'cs',
        'discount_id' => $discount->id,
        'conditions' => [
            'fg&notinstalledyet',
            'pp&uninstalled'
        ]
    ];
    $this->generateRepository->shouldReceive('createGenerate')
        ->with($dataCreateGenerate)
        ->andReturn($dataCreateGenerate);
    $result=$this->generateService->create($data);

    $this->assertEquals('cs',$result['app_name']);
    $this->assertEquals(["fg&notinstalledyet","pp&uninstalled"],$result['conditions']);
    $this->assertEquals(["message"=>"success","extend"=>"extend"],$result['success_message']);
    $this->assertEquals(["message"=>"fails","reason_expired"=>"time","reason_limit"=>"limit","reason_condition"=>"notMatch"],$result['fail_message']);
});

test('update generate fails when generate not found', function (){
    $this->generateRepository->shouldReceive('getGenerateById')
        ->with(1000000)
        ->andReturn(null);

    expect(function () {
        $this->generateService->update(1000000,[]);
    })->toThrow(NotFoundException::class,'Generate not found');
});

test('update generate fails when no coupon has code like GENAUTO% and missing value required', function (){
    $discount=Discount::factory()->make([
        'id'=>1,
        'name'>'discount',
        'type'=>'percentage',
        'expired_at' => Carbon::now()->addDays(1),
    ]);
    $coupon=Coupon::factory()->make([
        'discount_id'=>$discount->id,
        'code' => 'code1',
        'shop' => 'shop1',
    ]);

    $generate=Generate::factory()->make([
        'app_name' => 'cs',
        'discount_id' => $discount->id,
        'expired_range' => 50,
        'app_url' => 'http://localhost:8000/admin/generates_new',
    ]);
    $this->generateRepository->shouldReceive('getGenerateById')
        ->with($generate->id)
        ->andReturn($generate);

    $this->couponRepository->shouldReceive('getCouponByDiscountIdAndCode')
        ->with($generate->discount_id,'cs')
        ->andReturn(null);
    expect(function () use ($generate) {
        $this->generateService->update($generate->id,[]);
    })->toThrow(function(\Exception $exception){
        expect($exception->getErrors()['expired_range'][0])->tobe('The expired range field is required.');
        expect($exception->getErrors()['app_url'][0])->tobe('The app url field is required.');
        expect($exception->getErrors()['discount_app'][0])->tobe('The discount app field is required.');
    });
});
test('update generate fails when no coupon has code like GENAUTO% and discount new not found', function (){
    $discountIdNew=10000;
    $discount=Discount::factory()->make([
        'id'=>1,
        'name'=>'discount',
        'type'=>'percentage',
    ]);
    $generate=Generate::factory()->make([
        'app_name'=>'cs',
        'discount_id' => $discount->id,
        'expired_range' => 50,
        'app_url' => 'http://localhost:8000/admin/generates_new',
    ]);
    $this->generateRepository->shouldReceive('getGenerateById')
        ->with($generate->id)
        ->andReturn($generate);

    $this->couponRepository->shouldReceive('getCouponByDiscountIdAndCode')
        ->with($discount->id,'cs')
        ->andReturn(null);

    $dataUpdateGenerate = [
        'discount_app' =>$discountIdNew . '&cs',
        'expired_range' => 50,
        'app_url' => 'http://localhost:8000/admin/generates_new',
    ];
    //cho ra kết quả là generate 1
    $this->discountRepository->shouldReceive('findDiscountByIdNoCoupon')
        ->with($discountIdNew,'cs')
        ->andReturn(null);
    expect(function () use ($dataUpdateGenerate,$generate) {
        $this->generateService->update($generate->id,$dataUpdateGenerate);
    })->toThrow(NotFoundException::class,'Discount not found');
});
test('update generate fails when no coupon has code like GENAUTO% and discount new expired', function () {
    $discount = Discount::factory()->make([
        'id' => 1,
        'name' => 'discount',
        'type' => 'percentage',

    ]);
    $discountIdNew = 10000;
    $discountNew = Discount::factory()->make([
        'id' => $discountIdNew,
        'name' => 'discount',
        'type' => 'percentage',
        'expired_at' => now()->subDays(1),
    ]);
    $generate = Generate::factory()->make([
        'app_name' => 'cs',
        'discount_id' => $discount->id,
        'expired_range' => 50,
        'app_url' => 'http://localhost:8000/admin/generates_new',
    ]);
    $this->generateRepository->shouldReceive('getGenerateById')
        ->with($generate->id)
        ->andReturn($generate);

    $this->couponRepository->shouldReceive('getCouponByDiscountIdAndCode')
        ->with($discount->id, 'cs')
        ->andReturn(null);

    $dataUpdateGenerate = [
        'discount_app' => $discountIdNew . '&cs',
        'expired_range' => 50,
        'app_url' => 'http://localhost:8000/admin/generates_new',
    ];
    //cho ra kết quả là generate 1
    $this->discountRepository->shouldReceive('findDiscountByIdNoCoupon')
        ->with($discountIdNew, 'cs')
        ->andReturn($discountNew);
    expect(function () use ($dataUpdateGenerate, $generate) {
        $this->generateService->update($generate->id, $dataUpdateGenerate);
    })
        ->toThrow(function (DiscountException $exception) {
        expect($exception->getErrors()['error'][0])->tobe('Discount expired');
    });
});

test('update generate fails when no coupon has code like GENAUTO% and generate exist discount_id and app_name', function (){
    $discount=Discount::factory()->make([
            'id'=>1,
            'name'=>'discount',
            'type'=>'percentage',
        ]);
    $discountIdNew = 10000;
    $discountNew = Discount::factory()->make([
        'id' => $discountIdNew,
        'name' => 'discount',
        'type' => 'percentage',
        'expired_at' => now()->addDays(1),
    ]);
//    $coupon=Coupon::factory()->make([
//            'discount_id'=>$discount->id,
//            'code' => 'GENAUTO1000',
//            'shop' => 'shop1',
//        ]);
    $generate=Generate::factory()->make([
        'app_name'=>'cs',
        'discount_id' => $discount->id,
        'expired_range' => 50,
        'app_url' => 'http://localhost:8000/admin/generates_new',
    ]);
    $generateExistDiscountIdAndAppName=Generate::factory()->make([
        'app_name' => 'cs',
        'discount_id' => 10000,
        'expired_range' => 50,
        'app_url' => 'http://localhost:8000/admin/generates_new',
    ]);
    $this->generateRepository->shouldReceive('getGenerateById')
        ->with($generate->id)
        ->andReturn($generate);

    $this->couponRepository->shouldReceive('getCouponByDiscountIdAndCode')
        ->with($discount->id,'cs')
        ->andReturn(null);
    $this->discountRepository->shouldReceive('findDiscountByIdNoCoupon')
        ->with($discountIdNew, 'cs')
        ->andReturn($discountNew);

    $dataUpdateGenerate = [
        'discount_app' =>$discountIdNew . '&cs',
        'expired_range' => 50,
        'app_url' => 'http://localhost:8000/admin/generates_new',
    ];
    //cho ra kết quả là generate 1
    $this->generateRepository->shouldReceive('getGenerateByDiscountIdAndAppName')
        ->with($discountIdNew,'cs')
        ->andReturn($generateExistDiscountIdAndAppName);
    expect(function () use ($dataUpdateGenerate,$generate) {
        $this->generateService->update($generate->id,$dataUpdateGenerate);
    })->toThrow(function (GenerateException $exception){
       expect($exception->getErrors()['error'][0])->tobe('Generate existed discount_id');
    });
});
test('update generate fails when has coupon has code like GENAUTO% and missing value required', function (){
    $discount = Discount::factory()->make([
        'id' => 1,
        'name' => 'discount',
        'type' => 'percentage',
        'expired_at' => now()->addDays(1),
    ]);
    $coupon = Coupon::factory()->make([
        'discount_id' => $discount->id,
        'code' => 'GENAUTO100',
        'shop' => 'shop1',
    ]);

    $generate = Generate::factory()->make([
        'app_name' => 'cs',
        'discount_id' => $discount->id,
        'expired_range' => 50,
        'app_url' => 'http://localhost:8000/admin/generates_new',
    ]);

    $this->generateRepository->shouldReceive('getGenerateById')
        ->with($generate->id)
        ->andReturn($generate);

    $this->couponRepository->shouldReceive('getCouponByDiscountIdAndCode')
        ->with($discount->id, 'cs')
        ->andReturn($coupon);

    expect(function () use ($generate) {
        $this->generateService->update($generate->id, []);
    })->toThrow(function (GenerateException $exception) {
        expect($exception->getErrors()['expired_range'][0])->toBe('The expired range field is required.');
        expect($exception->getErrors()['app_url'][0])->toBe('The app url field is required.');
    });
});

test('update generate fails when no coupon has code like GENAUTO% but data has discount_app',function () {
    $discount = Discount::factory()->make([
        'id' => 1,
        'name' => 'discount',
        'type' => 'percentage',
        'expired_at' => now()->addDays(1),
    ]);
    $coupon = Coupon::factory()->make([
        'discount_id' => $discount->id,
        'code' => 'GENAUTO100',
        'shop' => 'shop1',
    ]);

    $generate=Generate::factory()->make([
        'app_name'=>'cs',
        'discount_id' => $discount->id,
        'expired_range' => 50,
        'app_url' => 'http://localhost:8000/admin/generates_new', // Giữ nguyên vì là URL cố định
        'limit' => 5,
        'conditions' => '[{"id":1,"apps":[{"name":"fg","status":"notinstalledyet"}]},{"id":2,"apps":[{"name":"pp","status":"uninstalled"}]}]', // Giữ nguyên chuỗi JSON
        'header_message' => 'header',
        'success_message' => [
            'message' => 'success',
            'extend' => 'extend'
        ],
        'used_message' => 'used',
        'fail_message' => [
            'message' => 'fails',
            'reason_expired' => 'time',
            'reason_limit' => 'limit',
            'reason_condition' => 'notMatch'
        ],
    ]);
    $this->generateRepository->shouldReceive('getGenerateById')
        ->with($generate->id)
        ->andReturn($generate);

    $this->couponRepository->shouldReceive('getCouponByDiscountIdAndCode')
        ->with($discount->id,'cs')
        ->andReturn($coupon);
    $this->discountRepository->shouldReceive('findDiscountByIdNoCoupon')
        ->with($discount->id, 'cs')
        ->andReturn($discount);

    $dataUpdateGenerate = [
        'discount_app' => $discount->id . '&cs',
        'expired_range' => 51,
        'app_url' => 'http://localhost:8000/admin/generates_new', // Giữ nguyên vì là URL cố định
        'limit' => 5,
        'condition_object' => '[{"id":1,"apps":[{"name":"fg","status":"notinstalledyet"}]},{"id":2,"apps":[{"name":"pp","status":"uninstalled"}]}]', // Giữ nguyên chuỗi JSON
        'header_message' => 'header',
        'success_message' => 'success',
        'used_message' => 'used',
        'fail_message' => 'fails',
        'extend_message' => 'extend',
        'reason_expired' => 'time',
        'reason_limit' => 'limit',
        'reason_condition' => 'notMatch'
    ];
    expect(function () use ($dataUpdateGenerate,$generate) {
        $this->generateService->update($generate->id,$dataUpdateGenerate);
    })->toThrow(function(GenerateException $exception){;
        expect($exception->getErrors()['error'][0])->toBe('Can not update discount');
    });
});
test('update generate success when no coupon has code like GENAUTHO%',function () {
    $discount = Discount::factory()->make([
        'id' => 1,
        'name' => 'discount',
        'type' => 'percentage',
        'expired_at' => now()->addDays(1),
    ]);
    $generate=Generate::factory()->make([
        'app_name'=>'cs',
        'discount_id' => $discount->id,
        'expired_range' => 50,
        'app_url' => 'http://localhost:8000/admin/generates_new', // Giữ nguyên vì là URL cố định
        'limit' => 5,
        'conditions' => '["fg&notinstalledyet","pp&uninstalled"]', // Giữ nguyên chuỗi JSON
        'header_message' => 'header',
        'success_message' => [
            'message' => 'success',
            'extend' => 'extend'
        ],
        'used_message' => 'used',
        'fail_message' => [
            'message' => 'fails',
            'reason_expired' => 'time',
            'reason_limit' => 'limit',
            'reason_condition' => 'notMatch'
        ],
    ]);
    $this->generateRepository->shouldReceive('getGenerateById')
        ->with($generate->id)
        ->andReturn($generate);

    $this->couponRepository->shouldReceive('getCouponByDiscountIdAndCode')
        ->with($discount->id,'cs')
        ->andReturn(null);
    $this->discountRepository->shouldReceive('findDiscountByIdNoCoupon')
        ->with($discount->id, 'cs')
        ->andReturn($discount);

    $dataUpdateGenerate = [
        'discount_app' => $discount->id . '&cs',
        'expired_range' => 51,
        'app_url' => 'http://localhost:8000/admin/generates_new', // Giữ nguyên vì là URL cố định
        'limit' => 5,
        'condition_object' => '[{"id":1,"apps":[{"name":"fg","status":"notinstalledyet"}]},{"id":2,"apps":[{"name":"pp","status":"uninstalled"}]}]', // Giữ nguyên chuỗi JSON
        'header_message' => 'header',
        'success_message' => 'success',
        'used_message' => 'used',
        'fail_message' => 'fails',
        'extend_message' => 'extend',
        'reason_expired' => 'time',
        'reason_limit' => 'limit',
        'reason_condition' => 'notMatch'
    ];

    $dataForMockUpdate=[
        "expired_range" => 51,
        "app_url" => "http://localhost:8000/admin/generates_new",
        "limit" => 5,
        "header_message" => "header",
        "success_message" => [
            "message" => "success",
            "extend" => "extend"
        ],
        "used_message" => "used",
        "fail_message" => [
            "message" => "fails",
            "reason_expired" => "time",
            "reason_limit" => "limit",
            "reason_condition" => "notMatch"
        ],
        "conditions" => ["fg&notinstalledyet", "pp&uninstalled"]
    ];
    $this->generateRepository->shouldReceive('updateGenerate')
        ->with($generate->id,$dataForMockUpdate)
        ->andReturn($dataForMockUpdate);
    $result=$this->generateService->update($generate->id,$dataUpdateGenerate);
    expect($result['conditions'])->toBe(['fg&notinstalledyet', 'pp&uninstalled']);
    expect($result['expired_range'])->toBe(51);

});
//
//test('private generate coupon fails when ip not support',function () {
//    $result=$this->generateService->privateGenerateCoupon('0.0.0.1',1,'shopName');
//    $this->assertEquals(
//        [
//        'status' => false,
//        'message' => 'Not support!',
//        ],$result
//        );
//});
//
//test('private generate coupon fails when generate not exist',function () {
//    $result=$this->generateService->privateGenerateCoupon('127.0.0.1',100000,'shopName');
//    $this->assertEquals(
//        [
//            'status' => false,
//            'message' => 'Generate not exist!',
//        ],$result
//    );
//});
//
//test('private generate coupon fails when discount not found',function (){
//    $discount=Discount::on('cs')->create([
//        'name'=>'discount',
//        'type'=>'percentage',
//    ]);
//    $generate=Generate::query()->create([
//        'app_name' => 'cs',
//        'discount_id' => 1,
//        'expired_range' => 50,
//        'app_url' => 'http://localhost:8000/admin/generates_new',
//        'status'=>false,
//    ]);
//    $result=$this->generateService->privateGenerateCoupon('127.0.0.1',$generate->id,'shopName');
//    $this->assertEquals([
//        'status' => false,
//        'message' => 'Discount not found!',
//    ],$result);
//
//});
//test('private generate coupon fails when generate status false',function (){
//    $discount=Discount::on('cs')->create([
//        'name'=>'discount',
//        'type'=>'percentage',
//        'expired_at'=>now()->addDays(1),
//    ]);
//    $generate=Generate::query()->create([
//        'app_name' => 'cs',
//        'discount_id' => $discount->id,
//        'expired_range' => 50,
//        'app_url' => 'http://localhost:8000/admin/generates_new',
//        'status'=>false,
//    ]);
//    $result=$this->generateService->privateGenerateCoupon('127.0.0.1',$generate->id,'shopName');
//    $this->assertEquals([
//        'status' => false,
//        'message' => 'Discount Expired!',
//    ],$result);
//
//});

