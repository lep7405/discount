<?php

namespace App\Http\Requests;

use App\Exceptions\DiscountException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

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
        $databaseName = explode('/', $this->route()->getPrefix())[1];
        $rules = [
            'name' => 'required|max:255|string',
            'expired_at' => 'nullable|date|after:started_at',
            'type' => 'required|in:percentage,amount',
            'value' => $this->percentageValidationRule(),
            'usage_limit' => 'nullable|integer|min:0',
            'trial_days' => 'nullable|integer|min:0',
        ];
        if (in_array($databaseName, ['affiliate', 'freegifts_new'])) {
            if ($this->input('discount_for_x_month') == '1') {
                $rules['discount_month'] = 'required|integer|min:1';
            }
        }

        return $rules;
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
        throw DiscountException::validateCreate($errorDetails);
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

    protected function percentageValidationRule()
    {
        return function ($attribute, $value, $fail) {
            if ($this->input('type') === 'percentage' && ($value < 0 || $value > 100)) {
                $fail($attribute.' must be between 0 and 100 when type is percentage.');
            }
            if ($this->input('type') === 'amount' && ($value < 0)) {
                $fail($attribute.' must be greater or equal to 0.');
            }
        };
    }
}
