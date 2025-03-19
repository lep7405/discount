<?php

namespace App\Repositories\Discount;

use App\Models\Discount;
use Illuminate\Support\Arr;
use Prettus\Repository\Eloquent\BaseRepository;

class DiscountRepositoryEloquent extends BaseRepository implements DiscountRepository
{
    public function model(): string
    {
        return Discount::class;
    }

    public function countDiscount(string $databaseName): int
    {
        return $this->getModel()
            ->on($databaseName)
            ->count();
    }
    public function getAll(string $databaseName,array $filters)
    {
        $perPage = Arr::get($filters, 'perPageDiscount');
        $search = Arr::get($filters, 'searchDiscount');
        $startedAt = Arr::get($filters, 'startedAt');
        $pageDiscount = Arr::get($filters, 'pageDiscount');

        return $this->getModel()
            ->on($databaseName)
            ->when($search, function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orwhere('started_at', 'like', "%{$search}%")
                    ->orwhere('expired_at', 'like', "%{$search}%")
                    ->orWhere(function ($sub) use ($search) {
                        if (is_numeric($search)) {
                            $sub->where('id', $search);
                        }
                    });
            })
            ->when($startedAt, function ($query) use ($startedAt) {
                $query->orderBy('started_at', $startedAt);
            })
            ->paginate($perPage, ['*'], 'pageDiscount', $pageDiscount);
    }


    public function findByIdWithCoupon(int $id, string $databaseName)
    {
        return $this->getModel()
            ->on($databaseName)
            ->with(['coupon'])
            ->find($id);
    }

    public function findById(int $id, string $databaseName)
    {
        return $this->getModel()
            ->on($databaseName)
            ->find($id);
    }

    public function createDiscount( string $databaseName,array $attributes)
    {
        return $this->getModel()
            ->on($databaseName)
            ->create($attributes);
//            ->create([
//                'name' => Arr::get($attributes, 'name'),
//                'value' => Arr::get($attributes, 'value'),
//                'type' => Arr::get($attributes, 'type'),
//                'started_at' => Arr::get($attributes, 'started_at'),
//                'expired_at' => Arr::get($attributes, 'expired_at'),
//                'usage_limit' => Arr::get($attributes, 'usage_limit'),
//                'trial_days' => Arr::get($attributes, 'trial_days'),
//                'discount_month' => Arr::get($attributes, 'discount_month'),
//            ]);
//            ->create($attributes);
    }

    public function updateDiscount(int $id, string $databaseName,array $attributes)
    {
        return $this->getModel()
            ->on($databaseName)
            ->where('id', $id)
            ->update($attributes);
        //
        //            ->update([
        //                'name' => Arr::get($attributes, 'name'),
        //                'value' => Arr::get($attributes, 'value'),
        //                'type' => Arr::get($attributes, 'type'),
        //                'started_at' => Arr::get($attributes, 'started_at'),
        //                'expired_at' => Arr::get($attributes, 'expired_at'),
        //                'usage_limit' => Arr::get($attributes, 'usage_limit'),
        //                'trial_days' => Arr::get($attributes, 'trial_days'),
        //                'discount_month' => Arr::get($attributes, 'discount_month'),
        //            ]);
    }

    public function getAllDiscountIdAndName($databaseName)
    {
        return $this->getModel()
            ->on($databaseName)
            ->select('id', 'name')
            ->get();
    }

    public function findByIdsAndApp($discountIds,$appName)
    {
        return $this->getModel()
            ->on($appName)
            ->whereIn('id', $discountIds)
            ->get();
    }

    public function getAllWithCoupon($databaseName)
    {
        return $this->getModel()
            ->on($databaseName)
            ->select('id')
            ->with(['coupon' => function ($query) {
                $query->select('id', 'times_used', 'discount_id');
            }])
            ->get();
    }


    public function deleteDiscount(int $id, string $databaseName)
    {
        return $this->getModel()
            ->on($databaseName)
            ->find($id)
            ->delete();
    }

    public function UpdateOrCreateDiscountInAffiliatePartner(string $connection,array $attributes)
    {
        return $this->getModel()
            ->on($connection)
            ->updateOrCreate(
                [
                    'name' => Arr::get($attributes, 'name'),
                    'type' => 'percentage',
                    'value' => Arr::get($attributes, 'value'),
                    'trial_days' => Arr::get($attributes, 'trial_days'),
                ],
                [
                    'usage_limit' => 1,
                ]
            );
//        return Discount::on($connection)->updateOrCreate(
//            [
//                'name' => Arr::get($attributes, 'name'),
//                'type' => 'percentage',
//                'value' => Arr::get($attributes, 'value'),
//                'trial_days' => Arr::get($attributes, 'trial_days'),
//            ],
//            [
//                'usage_limit' => 1,
//            ]
//        );
    }

    public function findByName(string $name, string $databaseName)
    {
        return $this->getModel()
            ->on($databaseName)
            ->where('name', $name)
            ->first();
    }

    public function getAllDiscountsWithCoupon($databaseName)
    {
        return $this->getModel()
            ->on($databaseName)
            ->select('id')
            ->with(['coupon' => function ($query) {
                $query->select('id', 'times_used');
            }])
            ->get();
    }
}
