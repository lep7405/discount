<?php

namespace App\Http\Requests;

use App\Exceptions\DiscountException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class CreateDiscountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function getDatabaseName(): string
    {
        return explode('/', $this->route()->getPrefix())[1];
    }

    public function validationData(): array
    {
        $databaseName = $this->getDatabaseName();
        $validationData = [
            'name' => $this->input('name'),
            'type' => $this->input('type'),
            'started_at' => $this->input('started_at'),
            'expired_at' => $this->input('expired_at'),
            'usage_limit' => $this->input('usage_limit'),
            'value' => $this->input('value'),
            'trial_days' => $this->input('trial_days'),
        ];
        if (in_array($databaseName, config('constant.SPECIAL_DATABASE_NAMES'))) {
            $validationData['discount_month'] = $this->input('discount_for_x_month') == '1'
                ? $this->input('discount_month')
                : null;
        }

        return $validationData;
    }

    public function rules(): array
    {
        $databaseName = $this->getDatabaseName();
        $rules = [
            'name' => 'required|max:255|string',
            'expired_at' => 'nullable|date|after:started_at',
            'type' => 'required|in:percentage,amount',
            'value' => $this->percentageValidationRule(),
            'usage_limit' => 'nullable|integer|min:0',
            'trial_days' => 'nullable|integer|min:0',
        ];
        if (in_array($databaseName, config('constant.SPECIAL_DATABASE_NAMES'))) {
            if ($this->input('discount_for_x_month') == '1') {
                $rules['discount_month'] = 'required|numeric|min:0';
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
                $errorDetails[$field][] = $message;
            }
        }
        throw DiscountException::validateCreate($errorDetails);
    }

    protected function percentageValidationRule()
    {
        return function ($attribute, $value, $fail) {
            if ($value && ! is_numeric($value)) {
                $fail($attribute . ' must be a number.');

                return;
            }
            $value = (float) $value;
            if ($this->input('type') === 'percentage' && ($value < 0 || $value > 100)) {
                $fail($attribute . ' must be between 0 and 100 when type is percentage.');
            } elseif ($this->input('type') === 'amount' && $value < 0) {
                $fail($attribute . ' must be greater or equal to 0.');
            }
        };
    }
}
