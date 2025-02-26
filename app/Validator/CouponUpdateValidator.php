<?php

namespace App\Validator;

use App\Exceptions\CouponException;
use Illuminate\Support\Facades\Validator;

class CouponUpdateValidator
{
    public static function validateEdit($data, $databaseName): array
    {
        $rules = [
            'code' => 'required|string|max:128',
            'discount_id' => "required|integer|min:1|exists:{$databaseName}.discounts,id",
            'shop' => 'nullable|string|max:128',
        ];
        $validator = Validator::make($data, $rules);

        //        if ($validator->fails()) {
        //            throw CouponException::validateEdit($validator->errors()->first());
        //        }
        return $validator->validated();
    }
}
