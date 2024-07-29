<?php

namespace App\Traits;

use App\Helpers\ApiResponse;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;

trait FailedValidationTrait
{
    protected function failedValidation(Validator $validator)
    {
        if ($this->is('api/*')) {
            $response = ApiResponse::sendResponse(422, 'Data Validation Error', $validator->errors());
            throw new ValidationException($validator, $response);
        }
    }
}
