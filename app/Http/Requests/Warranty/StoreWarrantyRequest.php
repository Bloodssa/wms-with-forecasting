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
            'email' => ['required', 'email'],
            'product_id' => ['required', 'exists:products,id'],
            'serial_number' => ['required', 'string', 'unique:warranties,serial_number'],
            'purchase_date' => ['required', 'date']
        ];
    }
}
