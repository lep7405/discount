<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class DiscountRequest extends FormRequest
{
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
        $rules = [
            'name' => 'required|max:255|string',
            'expired_at' => 'nullable|date|after:started_at',
            'type' => 'required|in:percentage,amount',
            'value' => [
                'numeric',
                'required',
                'min:0',
                function ($attribute, $value, $fail) {
                    if ($this->input('type') == 'percentage' && ($value < 0 || $value > 100)) {
                        $fail($attribute . ' is invalid.');
                    }
                },
            ],
            'usage_limit' => 'nullable|integer|min:0',
            'trial_days' => 'nullable|integer|min:0', //cái trial days cho null xong đó ở dưới lấy ra 0 nếu null
        ];
        if (in_array($databaseName, ['affiliate', 'freegifts_new'])) {
            $rules['discount_for_x_month'] = 'required|boolean';
            $rules['discount_month'] = 'required_if:discount_for_x_month,1|integer|min:1';
        }

        return $rules;
    }

    public function validationData(): array
    {
        return [
            'name' => $this->input('name'),
            'type' => $this->input('type'),
            'started_at' => $this->input('started_at'),
            'expired_at' => $this->input('expired_at'),
            'usage_limit' => $this->input('usage_limit'),
            'value' => $this->input('value'),
            'discount_for_x_month' => $this->input('discount_for_x_month'),
            'discount_month' => $this->input('discount_month'),
            'trial_days' => $this->input('trial_days', 0),
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'errors' => $validator->errors(),
        ], 422)); // 422 Unprocessable Entity
    }
}
