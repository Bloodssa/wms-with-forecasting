<?php

namespace App\Http\Requests\Warranty;

use Illuminate\Foundation\Http\FormRequest;

class InquiryWarrantyRequest extends FormRequest
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
            'warranty_id' => ['required', 'numeric', 'exists:warranties,id'],
            'message' => ['required', 'string'],
            'attachments' => ['nullable', 'array', 'max:10'],
            'attachments.*' => ['image', 'mimes:jpeg,png,jpg,webp', 'max:10240'],
        ];
    }

    public function messages(): array
    {
        return [
            'attachments.max' => 'You are only allowed to upload a maximum of 10 images.',
            'attachments.*.image' => 'Each file must be an image.',
            'attachments.*.mimes' => 'Only jpeg, png, jpg, webp formats are allowed.',
            'attachments.*.max' => 'Each image must not exceed 10MB.',
        ];
    }
}
