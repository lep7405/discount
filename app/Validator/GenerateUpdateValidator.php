<?php

namespace App\Validator;

use App\Exceptions\GenerateException;
use Illuminate\Support\Facades\Validator;

class GenerateUpdateValidator
{
    public static function validateUpdate($status, $data)
    {
        $rules = [
            'expired_range' => 'required|integer',
            'app_url' => 'required',
        ];
        if ($status) {
            $rules['discount_app'] = 'required';
        }
        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            $errors = $validator->errors();
            $errorDetails = [];

            foreach ($errors->messages() as $field => $messages) {
                foreach ($messages as $message) {
                    $errorDetails[$field][] = $message;
                }
            }
            throw GenerateException::validateUpdate($errorDetails);
        }
    }
}
