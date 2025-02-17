<?php

namespace App\Providers;

use App\Repositories\Coupon\CouponRepository;
use App\Repositories\Coupon\CouponRepositoryEloquent;
use App\Repositories\Discount\DiscountRepository;
use App\Repositories\Discount\DiscountRepositoryEloquent;
use App\Repositories\Generate\GenerateRepository;
use App\Repositories\Generate\GenerateRepositoryEloquent;
use App\Repositories\User\UserRepository;
use App\Repositories\User\UserRepositoryEloquent;
use App\Services\Coupon\CouponService;
use App\Services\Coupon\CouponServiceImp;
use App\Services\Discount\DiscountService;
use App\Services\Discount\DiscountServiceImp;
use App\Services\Generate\GenerateService;
use App\Services\Generate\GenerateServiceImp;
use App\Services\Report\ReportService;
use App\Services\Report\ReportServiceImp;
use App\Services\User\UserService;
use App\Services\User\UserServiceImp;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public array $bindings = [
        UserService::class => UserServiceImp::class,
        UserRepository::class => UserRepositoryEloquent::class,

        DiscountService::class => DiscountServiceImp::class,
        DiscountRepository::class => DiscountRepositoryEloquent::class,

        CouponService::class => CouponServiceImp::class,
        CouponRepository::class => CouponRepositoryEloquent::class,

        GenerateService::class => GenerateServiceImp::class,
        GenerateRepository::class => GenerateRepositoryEloquent::class,

        ReportService::class => ReportServiceImp::class,
    ];

    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer('*', function ($view) {
            $config = config('database.connections');
            $apps = [];

            foreach ($config as $key => $db) {
                if ($key && $key != 'mysql' && isset($db['app_name'])) {
                    $apps[$key] = $db['app_name'];
                }
            }

            $view->with('apps', $apps);
        });
    }
}
