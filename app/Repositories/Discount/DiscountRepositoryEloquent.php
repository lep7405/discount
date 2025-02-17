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

    public function findDiscountById(int $id, string $databaseName)
    {
        return Discount::on($databaseName)->with('coupon')->find($id);
    }

    public function findDiscountByIdNoCoupon(int $id, string $databaseName)
    {
        return $this->getModel()
            ->on($databaseName)
            ->find($id);
    }

    public function createDiscount(array $data, string $databaseName)
    {
        //        return Discount::on($databaseName)->create($data);
        return $this->getModel()
            ->on($databaseName)
            ->create($data);
    }

    public function destroyDiscount(int $id, string $databaseName): ?bool
    {
        return Discount::on($databaseName)->find($id)->delete();
    }

    public function updateDiscount(array $data, int $id, string $databaseName): bool
    {
        //        return Discount::on($databaseName)->find($id)->update($data);

        return $this->getModel()
            ->on($databaseName)
            ->where('id', $id)
            ->update($data);
    }

    //    public function getDiscounts(int $id){
    //        return $this->getModel()->where('id',$id)->first();
    //    }
    public function countDiscount(string $databaseName): int
    {
        //        return Discount::on($databaseName)->count();

        return $this->getModel()
            ->on($databaseName)
            ->count();
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
            ->with('coupon');
    }

    public function getAllDiscountsReports(array $filters, string $databaseName)
    {
        $perPage = Arr::get($filters, 'per_page_discount');
        $search = Arr::get($filters, 'search_discount');
        $started_at = Arr::get($filters, 'started_at');

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
            ->paginate($perPage);
    }

    public function getAllDiscountsNoPagination($databaseName)
    {
        return $this->getModel()
            ->on($databaseName)
            ->select('id', 'name')
            ->get(); // Lấy dữ liệu dưới dạng Collection

    }
}
