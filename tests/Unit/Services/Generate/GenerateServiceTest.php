<?php

namespace Tests\Unit\Services\Generate;

use App\Exceptions\DiscountException;
use App\Models\Coupon;
use App\Models\Discount;
use App\Models\Generate;
use App\Repositories\Coupon\CouponRepository;
use App\Repositories\Discount\DiscountRepository;
use App\Repositories\Generate\GenerateRepository;
use App\Services\Generate\GenerateService;
use Carbon\Carbon;
use Mockery;

uses(\Tests\TestCase::class);

beforeEach(function () {
    $this->generateRepository = Mockery::mock(GenerateRepository::class);
    $this->discountRepository = Mockery::mock(DiscountRepository::class);
    $this->couponRepository = Mockery::mock(CouponRepository::class);

    $this->generateService = app()->make(GenerateService::class, [
        $this->generateRepository,
        $this->discountRepository,
        $this->couponRepository,
    ]);

    // Xóa dữ liệu cũ trước khi chạy test
    Coupon::on('cs')->delete();
    Discount::on('cs')->delete();
    Generate::query()->delete();
});

//test('create generate fails when discount not found', function () {
//    $this->discountRepository->shouldReceive('findDiscountByIdNoCoupon')
//        ->with(1000000000, 'cs')
//        ->andReturn(null);
//
//    $data = [
//        'discount_app' => '1000000000&cs',
//    ];
//
//    expect(function () use ($data) {
//        $this->generateService->create($data);
//    })->toThrow(NotFoundException::class, 'Discount not found');
//});
test('test ',function (){

    $discount = new Discount();
    $discount->id = 1;
    $discount->name = 'discount';
    $discount->type = 'percentage';

    $discount->setConnection('cs');
    $discount->save();

//    $this->discountRepository->shouldReceive('findDiscountByName')
//        ->with( $discount->name, 'cs')
//        ->andReturn($discount);

    expect(function () use ($discount) {
        $this->generateService->testCreateName($discount->name,'cs');
    })->toThrow(function (DiscountException $e) {
        expect($e->getErrors()['error'][0])->toBe('Discount expired');
    });
});
//test('create generate fails when discount expired', function () {
////    $discount=Discount::on('cs')->create(
////        [
////            'id' => 1,
////            'name' => 'discount',
////            'type' => 'percentage',
////            'expired_at' => Carbon::now()->subDays(1), // Discount đã hết hạn
////        ]
////    );
//    $discount = new Discount();
//    $discount->id = 1;
//    $discount->name = 'discount';
//    $discount->type = 'percentage';
//    $discount->expired_at = Carbon::now()->subDays(1);
//    $discount->setConnection('cs'); // Chỉ định kết nối 'cs'
//    $discount->save(); // Lưu vào cơ sở dữ liệu
//
//    $this->discountRepository->shouldReceive('findDiscountByIdNoCoupon')
//        ->with( $discount->id, 'cs')
//        ->andReturn($discount);
//    expect(function () use ($discount) {
//        $this->generateService->create([
//            'discount_app' => $discount->id . '&cs',
//            'expired_range' => 50,
//            'app_url' => 'http://localhost:8000/admin',
//        ]);
//    })->toThrow(function (DiscountException $e) {
//        expect($e->getErrors()['error'][0])->toBe('Discount expired');
//    });
//});

//test('create generate fails when generate existed discount_id and app_name', function () {
//    $discount = Discount::on('cs')->create([
//        'name' => 'discount',
//        'type' => 'percentage',
//        'expired_at' => now()->addDays(1),
//    ]);
//
//    $this->discountRepository->shouldReceive('findDiscountByIdNoCoupon')
//        ->with($discount->id, 'cs')
//        ->andReturn($discount);
//
//    $generate = Generate::query()->create([
//        'app_name' => 'cs',
//        'discount_id' => $discount->id,
//        'expired_range' => 50,
//        'app_url' => 'http://localhost:8000/admin',
//    ]);
//    $this->generateRepository->shouldReceive('getGenerateByDiscountIdAndAppName')
//        ->with($discount->id, 'cs')
//        ->andReturn($generate);
//
//    $data = [
//        'discount_app' => $discount->id . '&cs',
//        'expired_range' => 50,
//        'app_url' => 'http://localhost:8000/admin',
//    ];
//    expect(function () use ($data) {
//        $this->generateService->create($data);
//    })->toThrow(function (GenerateException $e) {
//        expect($e->getErrors()['error'][0])->toBe('Generate existed discount_id and app_name');
//    });
//});
//
//test('test function handle condition', function () {
//    $condition='[{"id":1,"apps":[{"name":"fg","status":"notinstalledyet"},{"name":"ca","status":"installed"}]},{"id":2,"apps":[{"name":"pl","status":"charged"}]}]';
//    $result=$this->generateService->handleCondition($condition);
//    $this->assertEquals(["fg&notinstalledyet||ca&installed","pl&charged"],$result);
//});
//test('test function handle condition when condition not string json', function () {
//    $condition='[{id:1,"apps":[{"name":"fg","status":"notinstalledyet"},{"name":"ca","status":"installed"}]},{"id":2,"apps":[{"name":"pl","status":"charged"}]}]';
//    $result=$this->generateService->handleCondition($condition);
//    $this->assertEquals([],$result);
//});
//
//test('create generate success',function (){
//    $data = [
//        'name' => 'discount',
//        'type' => 'percentage',
//        'expired_at' => now()->addDays(1),
//    ];
//
//    $discount = Discount::on('cs')->create($data);
//
//    $this->discountRepository->shouldReceive('findDiscountByIdNoCoupon')
//        ->with($discount->id, 'cs')
//        ->andReturn($discount);
//    $data = [
//        'discount_app' => $discount->id . '&cs',
//        'expired_range' => 50,
//        'app_url' => 'http://localhost:8000/admin/generates_new', // Giữ nguyên vì là URL cố định
//        'limit' => 5,
//        'condition_object' => '[{"id":1,"apps":[{"name":"fg","status":"notinstalledyet"}]},{"id":2,"apps":[{"name":"pp","status":"uninstalled"}]}]', // Giữ nguyên chuỗi JSON
//        'header_message' => 'header',
//        'success_message' => 'success',
//        'used_message' => 'used',
//        'fail_message' => 'fails',
//        'extend_message' => 'extend',
//        'reason_expired' => 'time',
//        'reason_limit' => 'limit',
//        'reason_condition' => 'notMatch'
//    ];
//    $result=$this->generateService->create($data);
//    $this->assertEquals('cs',$result['app_name']);
//    $this->assertEquals('["fg&notinstalledyet","pp&uninstalled"]',$result['conditions']);
//    $this->assertEquals('{"message":"success","extend":"extend"}',$result['success_message']);
//    $this->assertEquals('{"message":"fails","reason_expired":"time","reason_limit":"limit","reason_condition":"notMatch"}',$result['fail_message']);
//});
//
//test('update generate fails when generate not found', function (){
//    $this->generateRepository->shouldReceive('getGenerateById')
//        ->with(1000000,'cs')
//        ->andReturn(null);
//
//    expect(function () {
//        $this->generateService->update(1000000,[]);
//    })->toThrow(NotFoundException::class,'Generate not found');
//});
//
//test('update generate fails when no coupon has code like GENAUTO% and missing value required', function (){
//    $discount=Discount::on('cs')->create([
//       'name'=>'discount',
//        'type'=>'percentage',
//    ]);
//    $coupon=Coupon::on('cs')->create([
//        'discount_id'=>$discount->id,
//        'code' => 'code1',
//        'shop' => 'shop1',
//    ]);
//
//    $generate=Generate::query()->create([
//        'app_name' => 'cs',
//        'discount_id' => $discount->id,
//        'expired_range' => 50,
//        'app_url' => 'http://localhost:8000/admin/generates_new',
//    ]);
//    $this->generateRepository->shouldReceive('getGenerateById')
//        ->with($generate->id)
//        ->andReturn($generate);
//
//    $this->couponRepository->shouldReceive('getCouponByDiscountIdAndCode')
//        ->with($coupon->id,'cs')
//        ->andReturn(null);
//    expect(function () use ($generate) {
//        $this->generateService->update($generate->id,[]);
//    })->toThrow(function(GenerateException $exception){
//        expect($exception->getErrors()['expired_range'][0])->tobe('The expired range field is required.');
//        expect($exception->getErrors()['app_url'][0])->tobe('The app url field is required.');
//        expect($exception->getErrors()['discount_app'][0])->tobe('The discount app field is required.');
//    });
//});
//test('update generate fails when no coupon has code like GENAUTO% and generate exist discount_id and app_name', function (){
//    $discount=Discount::on('cs')->create([
//        'name'=>'discount',
//        'type'=>'percentage',
//    ]);
//    $coupon=Coupon::on('cs')->create([
//        'discount_id'=>$discount->id,
//        'code' => 'code1',
//        'shop' => 'shop1',
//    ]);
//
//    $generate=Generate::query()->create([
//        'app_name' => 'cs',
//        'discount_id' => $discount->id,
//        'expired_range' => 50,
//        'app_url' => 'http://localhost:8000/admin/generates_new',
//    ]);
//    $generate1=Generate::query()->create([
//        'app_name' => 'cs',
//        'discount_id' => 10000,
//        'expired_range' => 50,
//        'app_url' => 'http://localhost:8000/admin/generates_new',
//    ]);
//    $this->generateRepository->shouldReceive('getGenerateById')
//        ->with($generate->id)
//        ->andReturn($generate);
//
//    $this->couponRepository->shouldReceive('getCouponByDiscountIdAndCode')
//        ->with($coupon->id,'cs')
//        ->andReturn($coupon);
//
//    $dataUpdateGenerate = [
//        'discount_app' => 10000 . '&cs',
//        'expired_range' => 50,
//        'app_url' => 'http://localhost:8000/admin/generates_new',
//    ];
//    //cho ra kết quả là generate 1
//    $this->generateRepository->shouldReceive('getGenerateByDiscountIdAndAppName')
//        ->with(10000,'cs')
//        ->andReturn($generate1);
//    expect(function () use ($dataUpdateGenerate,$generate) {
//        $this->generateService->update($generate->id,$dataUpdateGenerate);
//    })->toThrow(function (GenerateException $exception){
//       expect($exception->getErrors()['error'][0])->tobe('Generate existed discount_id');
//    });
//});
//test('update generate fails when has coupon has code like GENAUTO% and missing value required', function (){
//    $discount=Discount::on('cs')->create([
//        'name'=>'discount',
//        'type'=>'percentage',
//    ]);
//    $coupon=Coupon::on('cs')->create([
//        'discount_id'=>$discount->id,
//        'code' => 'GENAUTO100',
//        'shop' => 'shop1',
//    ]);
//
//    $generate=Generate::query()->create([
//        'app_name' => 'cs',
//        'discount_id' => $discount->id,
//        'expired_range' => 50,
//        'app_url' => 'http://localhost:8000/admin/generates_new',
//    ]);
//    $this->generateRepository->shouldReceive('getGenerateById')
//        ->with($generate->id)
//        ->andReturn($generate);
//
//    $this->couponRepository->shouldReceive('getCouponByDiscountIdAndCode')
//        ->with($coupon->id,'cs')
//        ->andReturn($coupon);
//    expect(function () use ($generate) {
//        $this->generateService->update($generate->id,[]);
//    })->toThrow(function(GenerateException $exception){
//        expect($exception->getErrors()['expired_range'][0])->tobe('The expired range field is required.');
//        expect($exception->getErrors()['app_url'][0])->tobe('The app url field is required.');
//    });
//});
//
//test('update generate fails when discount expired', function () {
//    $discount=Discount::on('cs')->create([
//        'name'=>'discount',
//        'type'=>'percentage',
//        'expired_at'=>now()->subDays(1),
//    ]);
//    $coupon=Coupon::on('cs')->create([
//        'discount_id'=>$discount->id,
//        'code' => 'GENAUTO100',
//        'shop' => 'shop1',
//    ]);
//
//    $generate=Generate::query()->create([
//        'app_name' => 'cs',
//        'discount_id' => $discount->id,
//        'expired_range' => 50,
//        'app_url' => 'http://localhost:8000/admin/generates_new',
//    ]);
//    $this->generateRepository->shouldReceive('getGenerateById')
//        ->with($generate->id)
//        ->andReturn($generate);
//
//    $this->couponRepository->shouldReceive('getCouponByDiscountIdAndCode')
//        ->with($coupon->id,'cs')
//        ->andReturn($coupon);
//    $this->discountRepository->shouldReceive('findDiscountByIdNoCoupon')
//        ->with($discount->id, 'cs')
//        ->andReturn($discount);
//
//    $dataUpdateGenerate = [
//        'expired_range' => 50,
//        'app_url' => 'http://localhost:8000/admin/generates_new',
//    ];
//    expect(function()use($generate,$dataUpdateGenerate){
//        $this->generateService->update($generate->id,$dataUpdateGenerate);
//    })->toThrow(function (DiscountException $exception){
//        expect($exception->getErrors()['error'][0])->toBe('Discount expired');
//    });
//});
//test('update generate success',function () {
//    $discount=Discount::on('cs')->create([
//        'name'=>'discount',
//        'type'=>'percentage',
//        'expired_at'=>now()->addDays(1),
//    ]);
//    $coupon=Coupon::on('cs')->create([
//        'discount_id'=>$discount->id,
//        'code' => 'GENAUTO100',
//        'shop' => 'shop1',
//    ]);
//
//    $generate=Generate::query()->create([
//        'app_name'=>'cs',
//        'discount_id' => $discount->id,
//        'expired_range' => 50,
//        'app_url' => 'http://localhost:8000/admin/generates_new', // Giữ nguyên vì là URL cố định
//        'limit' => 5,
//        'condition_object' => '[{"id":1,"apps":[{"name":"fg","status":"notinstalledyet"}]},{"id":2,"apps":[{"name":"pp","status":"uninstalled"}]}]', // Giữ nguyên chuỗi JSON
//        'header_message' => 'header',
//        'success_message' => 'success',
//        'used_message' => 'used',
//        'fail_message' => 'fails',
//        'extend_message' => 'extend',
//        'reason_expired' => 'time',
//        'reason_limit' => 'limit',
//        'reason_condition' => 'notMatch'
//    ]);
//    $this->generateRepository->shouldReceive('getGenerateById')
//        ->with($generate->id)
//        ->andReturn($generate);
//
//    $this->couponRepository->shouldReceive('getCouponByDiscountIdAndCode')
//        ->with($coupon->id,'cs')
//        ->andReturn($coupon);
//    $this->discountRepository->shouldReceive('findDiscountByIdNoCoupon')
//        ->with($discount->id, 'cs')
//        ->andReturn($discount);
//
//    $dataUpdateGenerate = [
//        'app_name'=>'cs',
//        'discount_id' => $discount->id,
//        'expired_range' => 51,
//        'app_url' => 'http://localhost:8000/admin/generates_new', // Giữ nguyên vì là URL cố định
//        'limit' => 5,
//        'condition_object' => '[{"id":1,"apps":[{"name":"fg","status":"notinstalledyet"}]},{"id":2,"apps":[{"name":"pp","status":"uninstalled"}]}]', // Giữ nguyên chuỗi JSON
//        'header_message' => 'header',
//        'success_message' => 'success',
//        'used_message' => 'used',
//        'fail_message' => 'fails',
//        'extend_message' => 'extend',
//        'reason_expired' => 'time',
//        'reason_limit' => 'limit',
//        'reason_condition' => 'notMatch'
//    ];
//
//    $result=$this->generateService->update($generate->id,$dataUpdateGenerate);
//    $this->assertEquals(1,$result);
//});
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

