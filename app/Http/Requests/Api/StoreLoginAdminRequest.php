<?php

namespace App\Http\Requests\Api;

use App\Traits\FailedValidationTrait;
use Illuminate\Foundation\Http\FormRequest;

class StoreLoginAdminRequest extends FormRequest
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
            'email' => 'required|string|max:100|email|exists:admins,email',
            'password' => 'required|string|min:6',
        ];
    }
    public function attributes()
    {
        return [
            'email' => 'Email',
            'password' => 'Password',
        ];
    }
}
