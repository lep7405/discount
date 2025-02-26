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

    public function countDiscount(string $databaseName) : int
    {
        return $this->getModel()
            ->on($databaseName)
            ->count();
    }

    // show update discount (cần coupon cho status )
    public function findDiscountByIdWithCoupon(int $id, string $databaseName)
    {
        return $this->getModel()
            ->on($databaseName)
            ->with(['coupon'])
            ->find($id);
    }



    public function findDiscountByIdNoCoupon(int $id, string $databaseName)
    {
        return $this->getModel()
            ->on($databaseName)
            ->find($id);
    }

    public function createDiscount(array $attributes, string $databaseName)
    {
        return $this->getModel()
            ->on($databaseName)
            ->create([
                'name' => Arr::get($attributes, 'name'),
                'value' => Arr::get($attributes, 'value'),
                'type' => Arr::get($attributes, 'type'),
                'started_at' => Arr::get($attributes, 'started_at'),
                'expired_at' => Arr::get($attributes, 'expired_at'),
                'usage_limit' => Arr::get($attributes, 'usage_limit'),
                'trial_days' => Arr::get($attributes, 'trial_days'),
                'discount_month' => Arr::get($attributes, 'discount_month'),
            ]);
    }

    public function updateDiscount(array $attributes, int $id, string $databaseName): bool
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



    public function getAllDiscounts(array $filters, string $databaseName)
    {
        $perPage = Arr::get($filters, 'per_page_discount');
        $search = Arr::get($filters, 'search_discount');
        $started_at = Arr::get($filters, 'started_at');

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
            ->when($started_at, function ($query) use ($started_at) {
                $query->orderBy('started_at', $started_at);
            })
            ->paginate($perPage);
    }

    public function getAllDiscountsForCreateOrUpdateCoupon($databaseName)
    {
        return $this->getModel()
            ->on($databaseName)
            ->select('id', 'name')
            ->get();
    }
    public function findDiscountsByIdsAndApp($discountIds, $appName)
    {
        return $this->getModel()
            ->on($appName)
            ->whereIn('id', $discountIds)
            ->get();
    }

    public function getAllNotFilterWithCoupon($databaseName)
    {
        return $this->getModel()
            ->on($databaseName)
            ->with('coupon')
            ->get();
    }

    public function getAllDiscountsReports(array $filters, string $databaseName)
    {
        $perPage = Arr::get($filters, 'per_page_discount');
        $search = Arr::get($filters, 'search_discount');
        $started_at = Arr::get($filters, 'started_at');
        $page_discount = Arr::get($filters, 'page_discount');

        return $this->getModel()
            ->on($databaseName)
            ->when($search, function ($query) use ($search) {
                $query->where('description', 'like', "%{$search}%")
                    ->orwhere('started_at', 'like', "%{$search}%")
                    ->orwhere('expired_at', 'like', "%{$search}%")
                    ->orWhere(function ($sub) use ($search) {
                        if (is_numeric($search)) {
                            $sub->where('id', $search);
                        }
                    });
            })
            ->when($started_at, function ($query) use ($started_at) {
                $query->orderBy('started_at', $started_at);
            })
            ->paginate($perPage, ['*'], 'page_discount', $page_discount);
    }

    public function getAllDiscountsNoCoupon($databaseName)
    {
        return $this->getModel()
            ->on($databaseName)
            ->select('id', 'name')
            ->get();

    }

    public function deleteDiscount(int $id, string $databaseName)
    {
        return $this->getModel()
            ->on($databaseName)
            ->find($id)
            ->delete();
    }
}
