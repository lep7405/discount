<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCouponRequest extends FormRequest
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
        //        dd($this->input('code'));

        return [
            'code' => 'required|string|max:255',
            'discount_id' => "required|integer|exists:{$databaseName}.discounts,id",
            'shop' => 'nullable|string|max:128',
        ];
    }
}
