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
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    public array $bindings = [
        UserRepository::class => UserRepositoryEloquent::class,
        DiscountRepository::class => DiscountRepositoryEloquent::class,
        CouponRepository::class => CouponRepositoryEloquent::class,
        GenerateRepository::class => GenerateRepositoryEloquent::class,
    ];

    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
