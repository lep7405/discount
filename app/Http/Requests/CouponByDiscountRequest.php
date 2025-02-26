<?php

namespace App\Http\Requests;

use App\Exceptions\CouponException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class CouponByDiscountRequest extends FormRequest
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
            'shop' => 'nullable|string|max:128',
        ];
    }
    public function validationData()
    {
        return [
            'code' => $this->input('code'),
            'shop' => $this->input('shop'),
        ];
    }
    public function failedValidation(Validator $validator)
    {
        $errors = $validator->errors();
        $errorDetails = [];

        foreach ($errors->messages() as $field => $messages) {
            foreach ($messages as $message) {
                $errorDetails[$field][] = $message;
            }
        }
        throw CouponException::validateCreateByDiscount($errorDetails);
    }
}
