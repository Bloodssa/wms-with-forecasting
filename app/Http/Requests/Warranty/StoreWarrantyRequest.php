<?php

namespace App\Http\Requests\Warranty;

use Illuminate\Foundation\Http\FormRequest;

class StoreWarrantyRequest extends FormRequest
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
            'claim_email' => ['required', 'email'],
            'multiple_products' => ['required', 'array', 'min:1'],
            'multiple_products.*.product_id' => [
                'required',
                'exists:products,id'
            ],
            'multiple_products.*.serial_number' => [
                'required',
                'string',
                'distinct',
                'unique:warranties,serial_number'
            ],
            'multiple_products.*.price' => [
                'required',
                'numeric',
                'min:0'
            ],
        ];
    }
}
