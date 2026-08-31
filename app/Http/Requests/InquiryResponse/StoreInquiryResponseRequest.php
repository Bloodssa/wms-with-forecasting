<?php

namespace App\Http\Requests\InquiryResponse;

use App\Enum\UserRole;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreInquiryResponseRequest extends FormRequest
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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'warranty_inquiries_id' => ['required', 'numeric', 'exists:warranty_inquiries,id'],
            'message' => ['required', 'string'],
            'attachments.*' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:5120']
        ];
    }
}
