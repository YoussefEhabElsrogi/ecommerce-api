<?php

namespace App\Http\Requests\Api;

use App\Traits\FailedValidationTrait;
use Illuminate\Foundation\Http\FormRequest;

class StoreContactRequest extends FormRequest
{
    use FailedValidationTrait;
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
            'name' => 'required|string',
            'email' => 'required|string|email',
            'phone' => ['required', 'regex:/^(010|011|012|015)[0-9]{8}$/'],
            'message' => 'required|string',
        ];
    }
    public function messages()
    {
        return [
            'phone.regex' => 'The phone number must be a valid Egyptian phone number starting with 010, 011, 012, or 015 followed by 8 digits.',
        ];
    }
}
