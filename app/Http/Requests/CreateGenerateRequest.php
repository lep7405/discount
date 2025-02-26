<?php

namespace App\Http\Requests;

use App\Exceptions\CouponException;
use App\Exceptions\GenerateException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class CreateGenerateRequest extends FormRequest
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
        return [
            'discount_app' => 'required',
            'expired_range' => 'required|integer',
            'app_url' => 'required|string',
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
        throw GenerateException::validateCreate($errorDetails);
    }

    public function validationData()
    {
        return [
            'discount_app' => $this->input('discount_app'),
            'expired_range' => $this->input('expired_range'),
            'app_url' => $this->input('app_url'),
            'limit' => $this->input('limit'),
            'condition_object' => $this->input('condition_object'),
            'header_message' => $this->input('header_message'),
            'success_message' => $this->input('success_message'),
            'used_message' => $this->input('used_message'),
            'fail_message' => $this->input('fail_message'),
            'extend_message' => $this->input('extend_message'),
            'reason_expired' => $this->input('reason_expired'),
            'reason_limit' => $this->input('reason_limit'),
            'reason_condition' => $this->input('reason_condition'),
        ];
    }
}
