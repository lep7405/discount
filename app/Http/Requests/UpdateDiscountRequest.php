<?php

namespace App\Http\Requests;

use App\Models\Discount;
use Illuminate\Foundation\Http\FormRequest;

class UpdateDiscountRequest extends FormRequest
{
    protected $discount;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $routeName = $this->route()->getPrefix();
        $arr = explode('/', $routeName);
        $databaseName = $arr[1];

        $this->discount = Discount::on($databaseName)->with('coupon')->findOrFail($this->route()->parameter('id'));
        $discount_status = $this->discount->coupon->contains(function ($item) {
            return $item->times_used > 0;
        });
        $rules = [
            'name' => 'required|max:255|string',
            'expired_at' => 'date|after:started_at',
            'usage_limit' => 'nullable|integer|min:0',
        ];
        //nếu mà cái discount mà chưa có coupon nào đuợc sử dụng thì cần phái nhập lớp nây
        if (! $discount_status) {
            $rules['type'] = 'required|in:percentage,fixed';
            $rules['value'] = 'required|numeric|between:0,100';
            $rules['trial_days'] = 'nullable|numeric|min:0';

            if (in_array($databaseName, ['affiliate', 'freegifts_new'])) {
                $rules['discount_for_x_month'] = 'required|boolean';
                $rules['discount_month'] = 'required_if:discount_for_x_month,1|nullable|numeric|min:0';
            }
        }

        return $rules;
    }

    public function validationData(): array
    {
        $data = [
            'name' => $this->input('name'),
            'started_at' => $this->input('started_at'),
            'expired_at' => $this->input('expired_at'),
            'usage_limit' => $this->input('usage_limit'),
        ];

        // Nếu discount_status là false, giữ lại các trường này
        if (! $this->input('discount_status')) {
            $data['type'] = $this->input('type');
            $data['value'] = $this->input('value');
            $data['trial_days'] = $this->input('trial_days');

            if (in_array($this->input('databaseName'), ['affiliate', 'freegifts_new'])) {
                $data['discount_for_x_month'] = $this->input('discount_for_x_month');
                $data['discount_month'] = $this->input('discount_month');
            }
        }

        return $data;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'databaseName' => $this->route('databaseName'),
            'discount_status' => $this->route('discount_status'),
        ]);
    }
}
