<?php

namespace App\Http\Requests\Api;

use App\Traits\FailedValidationTrait;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCategoryRequest extends FormRequest
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
        $slug = $this->route('slug'); // Extract the slug from the route

        return [
            'name' => [
                'required',
                'string',
                'between:2,100',
                Rule::unique('categories', 'name')->ignore($slug, 'slug'),
            ],
            'image' => 'nullable|image|mimes:png,jpg|max:2048',
        ];
    }

    /**
     * Get custom attribute names.
     *
     * @return array<string, string>
     */
    public function attributes()
    {
        return [
            'name' => 'Name',
            'image' => 'Image'
        ];
    }
}
