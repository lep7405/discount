<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class CouponRequest extends FormRequest
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

        return [
            'code' => "required|string|max:128|unique:{$databaseName}.coupons,code",
            'discount_id' => "required|integer|min:1|exists:{$databaseName}.discounts,id",
            'shop' => 'nullable|string|max:128',
        ];
        //dùng nháp đơn và nháy kép trong cái return có gì khác nhau à
    }
    //    protected function failedValidation(Validator $validator)
    //    {
    //        throw new HttpResponseException(response()->json([
    //            'success' => false,
    //            'errors' => $validator->errors(),
    //        ], 422)); // 422 Unprocessable Entity
    //    }
}
