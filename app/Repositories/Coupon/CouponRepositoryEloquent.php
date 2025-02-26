<?php

namespace App\Repositories\Coupon;

use App\Models\Coupon;
use Illuminate\Support\Arr;
use Prettus\Repository\Eloquent\BaseRepository;

class CouponRepositoryEloquent extends BaseRepository implements CouponRepository
{
    public function model(): string
    {
        return Coupon::class;
    }

    public function getAllCoupons(array $filters, string $databaseName)
    {
        $perPage = Arr::get($filters, 'per_page_coupon');
        $search = Arr::get($filters, 'search_coupon');
        $status = Arr::get($filters, 'status');
        $arrange_times_used = Arr::get($filters, 'time_used');

        return $this->getModel()
            ->on($databaseName)
            ->with(['discount:id,name'])
            ->when($search, function ($query) use ($search) {
                $query->where('code', 'like', "%{$search}%")
                    ->orwhere('shop', 'like', "%{$search}%")
                    ->orWhereHas('discount', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    })
                    ->orwhere('times_used', $search);
            })
            ->when($status !== null, function ($query) use ($status) {
                $query->where('status', $status);
            })
            ->when($arrange_times_used, function ($query) use ($arrange_times_used) {
                $query->orderBy('times_used', $arrange_times_used);
            })
            ->paginate($perPage);
    }

    public function countCoupons(string $databaseName)
    {
        return $this->getModel()
            ->on($databaseName)
            ->count();
    }

    public function createCoupon(array $data, string $databaseName)
    {
        return $this->getModel()
            ->on($databaseName)
            ->create([
                'code' => Arr::get($data, 'code'),
                'shop' => Arr::get($data, 'shop'),
                'discount_id' => Arr::get($data, 'discount_id'),
                'automatic' => Arr::get($data, 'automatic') || false,
                'times_used' => Arr::get($data, 'times_used'),
            ]);
    }

    public function getCouponById(int $id, string $databaseName)
    {
        return $this->getModel()
            ->on($databaseName)
            ->find($id);
    }

    public function decrementTimesUsed(int $id, int $numDecrement, string $databaseName)
    {
        return $this->getModel()
            ->on($databaseName)
            ->where('id', $id)
            ->decrement('times_used', $numDecrement);
    }

    public function getCouponByCode(string $code, string $databaseName)
    {
        return $this->getModel()
            ->on($databaseName)
            ->where('code', $code)
            ->first();
    }

    public function updateCoupon(array $data, int $id, string $databaseName)
    {
        return $this->getModel()
            ->on($databaseName)
            ->where('id', $id)
            ->update($data);
    }

    public function getCouponByDiscountIdAndCode(int $discountId, string $databaseName)
    {
        return $this->getModel()
            ->on($databaseName)
            ->where('discount_id', $discountId)
            ->where('code', 'like', 'GENAUTO%')
            ->get();
    }

    public function deleteCoupon(int $id, string $databaseName)
    {
        return $this->getModel()
            ->on($databaseName)
            ->where('id', $id)
            ->delete();
    }

    public function getAllCouponsReport(array $filters, string $databaseName)
    {
        $perPage = Arr::get($filters, 'per_page_discount');
        $search = Arr::get($filters, 'search_discount');
        $status = Arr::get($filters, 'status');
        $arrange_times_used = Arr::get($filters, 'time_used');
        $page_coupon = Arr::get($filters, 'page_coupon');

        return $this->getModel()
            ->on($databaseName)
            ->with(['discount:id,name'])
            ->when($search, function ($query) use ($search) {
                $query->where('code', 'like', "%{$search}%")
                    ->orwhere('shop', 'like', "%{$search}%")
                    ->orWhereHas('discount', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    })
                    ->orwhere('times_used', $search);
            })
            ->when($status !== null, function ($query) use ($status) {
                $query->where('status', $status);
            })
            ->when($arrange_times_used, function ($query) use ($arrange_times_used) {
                $query->orderBy('times_used', $arrange_times_used);
            })
            ->paginate($perPage, ['*'], 'page_coupon', $page_coupon);
    }

    public function getCouponByDiscountIdandShop($discountId, $shopName, $databaseName)
    {
        return $this->getModel()
            ->on($databaseName)
            ->where('discount_id', $discountId)
            ->where('shop', $shopName)
            ->first();
    }

    public function deleteCouponByDiscountId(int $discountId, string $databaseName)
    {
        return $this->getModel()
            ->on($databaseName)
            ->where('discount_id', $discountId)
            ->delete();
    }

    public function getAllCouponsByDiscount($discount_id, array $filters, string $databaseName)
    {
        $perPage = Arr::get($filters, 'per_page_coupon');
        $search = Arr::get($filters, 'search_coupon');
        $status = Arr::get($filters, 'status');
        $arrange_times_used = Arr::get($filters, 'time_used');

        return $this->getModel()
            ->on($databaseName)
            ->with(['discount:id,name'])
            ->where('discount_id', $discount_id)
            ->when($search, function ($query) use ($search) {
                $query->where('code', 'like', "%{$search}%")
                    ->orwhere('shop', 'like', "%{$search}%")
                    ->orWhereHas('discount', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    })
                    ->orWhere(function ($sub) use ($search) {
                        if (is_numeric($search)) {
                            $sub->where('id', $search);
                        }
                    })
                    ->orwhere('times_used', 'like', "%{$search}%");
            })
            ->when($status !== null, function ($query) use ($status) {
                $query->where('status', $status);
            })
            ->when($arrange_times_used, function ($query) use ($arrange_times_used) {
                $query->orderBy('times_used', $arrange_times_used);
            })
            ->paginate($perPage);
    }
}
