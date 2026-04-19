<?php

namespace App\Http\Requests\Product;

use Illuminate\Foundation\Http\FormRequest;
use App\Enum\UserRole;

class UpdateProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = $this->user();

        return $user && $user->hasAnyRole([
            UserRole::ADMIN->value,
            UserRole::STAFF->value,
            UserRole::TECHNICIAN->value
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'category_id' => ['required', 'string'],
            'brand' => ['required', 'string'],
            'warranty_duration' => ['required', 'integer', 'min:0', 'max:200'],
            'service_center_name' => ['required', 'string'],
            'service_center_address' => ['required', 'string'],
            'product_image_url' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:5120']
        ];
    }
}
