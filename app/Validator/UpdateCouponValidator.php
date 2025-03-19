<?php

namespace App\Validator;

use App\Exceptions\CouponException;
use Illuminate\Support\Facades\Validator;

class UpdateCouponValidator
{
    public static function validateUpdate($databaseName,$data): array
    {
        $rules = [
            'code' => 'required|string|max:128',
            'discount_id' => "required|integer|min:1|exists:{$databaseName}.discounts,id",
            'shop' => 'nullable|string|max:128',
        ];
        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            $errors = $validator->errors();
            $errorDetails = [];

            foreach ($errors->messages() as $field => $messages) {
                foreach ($messages as $message) {
                    $errorDetails[$field][] = $message;
                }
            }
            throw CouponException::validateUpdate($errorDetails);
        }
        return $validator->validated();
    }
}
