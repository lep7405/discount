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

    public function getAll(string $databaseName, array $filters)
    {
        $perPage = Arr::get($filters, 'perPageCoupon');
        $pageCoupon = Arr::get($filters, 'pageCoupon', 1);
        $search = Arr::get($filters, 'searchCoupon');
        $status = Arr::get($filters, 'status');
        $arrange_times_used = Arr::get($filters, 'timeUsed');
        $discount_id = Arr::get($filters, 'discountId');

        return $this->getModel()
            ->on($databaseName)
            ->with(['discount:id,name'])
            ->when($discount_id, function ($query) use ($discount_id) {
                $query->where('discount_id', $discount_id);
            })
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
            ->paginate($perPage, ['*'], 'pageCoupon', $pageCoupon);
    }


    public function countCoupons(string $databaseName)
    {
        return $this->getModel()
            ->on($databaseName)
            ->count();
    }

    public function createCoupon(string $databaseName, array $attributes)
    {
        return $this->getModel()
            ->on($databaseName)
            ->create([
                'code' => Arr::get($attributes, 'code'),
                'shop' => Arr::get($attributes, 'shop'),
                'discount_id' => Arr::get($attributes, 'discountId'),
                'automatic' => Arr::get($attributes, 'automatic', 0),
                'status' => Arr::get($attributes, 'status', 1),
                'times_used' => Arr::get($attributes, 'timesUsed'),
            ]);
    }

    public function findById($id, string $databaseName)
    {
        return $this->getModel()
            ->on($databaseName)
//            ->with(['discount:id,name'])
            ->find($id);
    }

    public function decrementTimesUsed(int $id, string $databaseName, int $numDecrement)
    {
        return $this->getModel()
            ->on($databaseName)
            ->where('id', $id)
            ->decrement('times_used', $numDecrement);
    }

    public function findByCode(string $code, string $databaseName)
    {
        return $this->getModel()
            ->on($databaseName)
            ->where('code', $code)
            ->first();
    }

    public function updateCoupon(int $id, string $databaseName, array $data)
    {
        return $this->getModel()
            ->on($databaseName)
            ->where('id', $id)
            ->update($data);
    }

    public function findByDiscountIdAndCode(int $discountId, string $databaseName)
    {
        return $this->getModel()
            ->on($databaseName)
            ->where('discount_id', $discountId)
            ->where('code', 'like', 'GENAUTO%')
            ->first();
    }

    public function deleteCoupon(int $id, string $databaseName)
    {
        return $this->getModel()
            ->on($databaseName)
            ->where('id', $id)
            ->delete();
    }

    public function findByDiscountIdandShop($discountId, $shopName, $databaseName)
    {
        return $this->getModel()
            ->on($databaseName)
            ->where('discount_id', $discountId)
            ->where('shop', $shopName)
            ->orderBy('created_at', 'desc')
            ->first();
    }

    public function deleteByDiscountId(int $discountId, string $databaseName)
    {
        return $this->getModel()
            ->on($databaseName)
            ->where('discount_id', $discountId)
            ->delete();
    }

    public function countByDiscountIdAndCode(int $discountId, string $databaseName)
    {
        return $this->getModel()
            ->on($databaseName)
            ->where('discount_id', $discountId)
            ->where('code', 'like', 'GENAUTO%')
            ->count();
    }
}
