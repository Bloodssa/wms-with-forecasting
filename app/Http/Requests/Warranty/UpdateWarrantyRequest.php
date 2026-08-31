<?php

namespace App\Http\Requests\Warranty;

use App\Enum\UserRole;
use App\Enum\WarrantyStatusType;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateWarrantyRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->role === UserRole::ADMIN;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'serial_number' => ['required', 'string', 'max:255', Rule::unique('warranties', 'serial_number')->ignore($this->route('warranty')?->id)],
            'purchase_date' => ['required', 'date'],
            'expiry_date' => ['required', 'date', 'after_or_equal:purchase_date'],
            'status' => ['required', Rule::enum(WarrantyStatusType::class)],
        ];
    }
}
