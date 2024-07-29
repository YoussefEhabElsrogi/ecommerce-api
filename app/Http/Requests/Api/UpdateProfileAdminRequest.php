<?php

namespace App\Http\Requests\Api;

use App\Traits\FailedValidationTrait;
use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileAdminRequest extends FormRequest
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
        // Get the admin ID from the route parameters
        $adminId = $this->route('admin_id');

        return [
            'name' => 'required|string|between:2,100',
            'email' => 'required|email|max:100|unique:admins,email,' . $adminId,
            'image' => 'nullable|image|mimes:png,jpg|max:2048',
        ];
    }
    public function attributes()
    {
        return [
            'name' => 'Name',
            'email' => 'Email',
            'image' => 'Image',
        ];
    }
}
