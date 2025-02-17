<?php

namespace App\Services\Discount;

use App\Exceptions\DiscountException;
use App\Repositories\Discount\DiscountRepository;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class DiscountServiceImp implements DiscountService
{
    public function __construct(protected DiscountRepository $discountRepository) {}

    public function store(array $data, $databaseName)
    {
        return $this->discountRepository->createDiscount($data, $databaseName);
    }

    /**
     * @throws DiscountException
     * @throws ValidationException
     */
    public function edit($id, array $data, $databaseName)
    {
        $array = $this->findDiscountById($id, $databaseName);
        $discount = $array['discount'];
        $discount_status = $array['discount_status'];
        $validatedData = $this->validateEdit($data, $discount_status, $databaseName);
        $discount->update($validatedData);

        return $discount;
    }

    public function destroy($id, $databaseName): void
    {
        $discount = $this->discountRepository->findDiscountById($id, $databaseName);
        if (! $discount) {
            throw DiscountException::notFound();
        }
        $discount_status = $discount->coupon->contains(function ($item) {
            return $item->times_used > 0;
        });
        if ($discount_status) {
            throw DiscountException::canNotDelete();
        } else {
            $discount->coupon()->delete();
            $discount->delete();
        }
    }

    public function findDiscountById($id, $databaseName): array
    {
        $discount = $this->discountRepository->findDiscountById($id, $databaseName);
        $discount_status = $discount->coupon->contains(function ($item) {
            return $item->times_used > 0;
        });

        return [
            'discount' => $discount,
            'discount_status' => $discount_status,
        ];
    }

    public function getAllDiscounts(array $filters, $databaseName)
    {

        $count_all = $this->discountRepository->countDiscount($databaseName);
        $perPage = Arr::get($filters, 'per_page_discount', 5);
        $perPage = $perPage == -1 ? $count_all : $perPage;
        $started_at = Arr::get($filters, 'started_at');
        if ($started_at && ! in_array($started_at, ['desc', 'asc'])) {
            throw DiscountException::inValidStartedAt();
        }
        Arr::set($filters, 'per_page_discount', $perPage);
        Arr::set($filters, 'started_at', $started_at);
        \Illuminate\Log\log($filters);
        $discountData = $this->discountRepository->getAllDiscounts($filters, $databaseName);
        $total_items = $discountData->total();
        $total_pages = $discountData->lastPage();
        $current_pages = $discountData->currentPage();

        return [
            'discountData' => $discountData,
            'total_pages_discount' => $total_pages,
            'total_items_discount' => $total_items,
            'current_pages_discount' => $current_pages,
        ];

    }

    public function getDiscountNoCoupon(int $id, string $databaseName)
    {
        return $this->discountRepository->findDiscountByIdNoCoupon($id, $databaseName);
    }

    /**
     * @throws ValidationException
     * @throws DiscountException
     */
    private function validateEdit($data, $discount_status, $databaseName): array
    {
        $rules = [
            'name' => 'required|max:255|string',
            'expired_at' => 'date|after:started_at',
            'usage_limit' => 'nullable|integer|min:0',
        ];
        if (! $discount_status) {
            $rules['type'] = 'required|in:percentage,amount';
            if ($data['type'] == 'percentage') {
                $rules['value'] = 'required|numeric|between:0,100';
            } elseif ($data['type'] == 'amount') {
                $rules['value'] = 'required|numeric|min:0';
            }
            $rules['trial_days'] = 'nullable|integer|min:0';
            if (in_array($databaseName, ['affiliate', 'freegifts_new'])) {
                $rules['discount_for_x_month'] = 'required|boolean';
                $rules['discount_month'] = 'required_if:discount_for_x_month,1|nullable|numeric|min:0';
            }
        }

        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            throw DiscountException::validateEdit($validator->errors()->first());
        }

        return $validator->validated();
    }
}
