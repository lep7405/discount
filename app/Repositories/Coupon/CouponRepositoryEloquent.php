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
            ->create($data);
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
}
