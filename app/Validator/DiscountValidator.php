<?php

namespace App\Validator;

use App\Exceptions\DiscountException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class DiscountValidator
{
    /**
     * @throws DiscountException
     * @throws ValidationException
     */
    public static function validateEdit($data, $discount_status, $databaseName): array
    {
        $rules = [
            'name' => 'required|max:255|string',
            'started_at' => 'nullable|date',
            'expired_at' => 'nullable|date|after:started_at',
            'usage_limit' => 'nullable|integer|min:0',
        ];
        if ($discount_status) {
            $invalidFields = array_keys(Arr::only($data, ['type', 'value', 'trial_days', 'discount_for_x_month', 'discount_month']));
            if (! empty($invalidFields)) {
                $errorDetails = ['error' => ['Cannot update type, value, trial_days, discount_for_x_month after discount is used.']];
                throw DiscountException::validateUpdate($errorDetails);
            }
        }
        if (! $discount_status) {
            $rules['type'] = 'required|in:percentage,amount';
            if ($data['type'] === 'percentage') {
                $rules['value'] = 'nullable|numeric|between:0,100';
            } elseif ($data['type'] === 'amount') {
                $rules['value'] = 'nullable|numeric|min:0';
            }
            $rules['trial_days'] = 'nullable|integer|min:0';

            if (in_array($databaseName, ['affiliate', 'freegifts_new'])) {
//                dd($data['discount_for_x_month']);
//                $rules['discount_for_x_month'] = 'required|in:0,1';
                if ($data['discount_for_x_month'] === '1') {
                    $rules['discount_month'] = 'required|integer|min:1';
                }

            }
        }
        $validator = Validator::make($data, $rules);
        if ($validator->fails()) {
            $errors = $validator->errors();
            $errorDetails = [];

            foreach ($errors->messages() as $field => $messages) {
                foreach ($messages as $message) {
                    $errorDetails[$field][] = $message; // Đảm bảo đúng định dạng Laravel cần
                }
            }
            throw DiscountException::validateUpdate($errorDetails);
        }
        return $validator->validated();
    }
}
