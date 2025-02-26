<?php

namespace App\Http\Requests;

use App\Exceptions\CouponException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

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
        $databaseName = explode('/', $this->route()->getPrefix())[1];

        return [
            'code' => "required|string|max:128|unique:{$databaseName}.coupons,code",
            'discount_id' => "required|integer|min:1|exists:{$databaseName}.discounts,id",
            'shop' => 'nullable|string|max:128',
        ];
        // dùng nháp đơn và nháy kép trong cái return có gì khác nhau à
    }

    public function validationData(): array
    {
        return [
            'code' => $this->input('code'),
            'discount_id' => $this->input('discount_id'),
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
        throw CouponException::validateCreate($errorDetails);
    }
}
