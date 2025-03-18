<?php

namespace App\Repositories\Generate;

use App\Models\Generate;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Prettus\Repository\Eloquent\BaseRepository;

class GenerateRepositoryEloquent extends BaseRepository implements GenerateRepository
{
    public function model()
    {
        return Generate::class;
    }

    public function getAll(array $filters)
    {
        $perPage = Arr::get($filters, 'per_page');
        $search = Arr::get($filters, 'search');
        $status = Arr::get($filters, 'status', null);
        $cs = DB::connection('cs')->table('discounts')->select('id as ids', 'name');
        $affiliate = DB::connection('affiliate')->table('discounts')->select('id as ids', 'name');

        return Generate::query()
            ->leftJoinSub($affiliate, 'discounts_affiliate', function ($join) {
                $join->on('generates.discount_id', '=', 'discounts_affiliate.ids');
            })
            ->leftJoinSub($cs, 'discounts_cs', function ($join) {
                $join->on('generates.discount_id', '=', 'discounts_cs.ids');
            })

            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('conditions', 'like', "%{$search}%")
                        ->orWhere(function ($sub) use ($search) {
                            if (is_numeric($search)) {
                                $sub->where('id', $search);
                            }
                        })
                        ->orWhereRaw("(CASE
                    WHEN app_name = 'cs' THEN 'currency switcher'
                    WHEN app_name = 'affiliate' THEN 'affiliate'

                    ELSE app_name
                END) LIKE ?", ["%{$search}%"])
                        ->orWhere('conditions', 'like', "%{$search}%")
                        ->orWhere('expired_range', 'like', "%{$search}%")
                        ->orWhere('app_url', 'like', "%{$search}%")
                        ->orWhere('discounts_cs.name', 'like', "%{$search}%")
                        ->orWhere('discounts_affiliate.name', 'like', "%{$search}%");
                });
            })
            ->when($status !== null, function ($query) use ($status) {
                $query->where('status', $status);
            })
            ->paginate($perPage);
    }

    public function countGenerate()
    {
        return $this->getModel()
            ->count();
    }

    public function findByDiscountIdAndAppName($discount_id, $app_name)
    {
        return $this->getModel()
            ->where('discount_id', $discount_id)
            ->where('app_name', $app_name)
            ->first();
    }

    public function createGenerate(array $attributes)
    {
        return $this->getModel()
            ->create($attributes);
    }

    public function updateGenerate($id, array $data)
    {
        return $this->getModel()
            ->where('id', $id)
            ->update($data);
    }

    public function updateGenerateStatus($id, $status)
    {
        return $this->getModel()
            ->where('id', $id)
            ->update(['status' => ! $status]);
    }

    public function destroyGenerate($id)
    {
        return $this->getModel()
            ->where('id', $id)
            ->delete();
    }

    public function findById($id)
    {
        return $this->getModel()
            ->where('id', $id)
            ->first();
    }
}
