<?php

namespace App\Http\Requests;

use App\Exceptions\AuthException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class ChangePasswordRequest extends FormRequest
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
            'password' => 'required|string|min:6|confirmed',
        ];
    }

    public function validationData(): array
    {
        return [
            'password' => $this->input('password'),
            'password_confirmation' => $this->input('password_confirmation'),
        ];
    }

    public function failedValidation(Validator $validator)
    {
        $errors = $validator->errors();
        $errorDetails = [];

        foreach ($errors->messages() as $field => $messages) {
            foreach ($messages as $message) {
                $errorDetails[$field][] = $message; // Đảm bảo đúng định dạng Laravel cần
            }
        }
        throw AuthException::validateChangePassWord($errorDetails);
    }
}
