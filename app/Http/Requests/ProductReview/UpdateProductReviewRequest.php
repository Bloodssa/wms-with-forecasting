<?php

namespace App\Http\Requests\ProductReview;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateProductReviewRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->review);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['nullable','string'],
            'removed_attachments' => ['nullable','array'],
            'removed_attachments.*' => ['string'],
            'attachments' => ['nullable','array','max:10'],
            'attachments.*' => ['image','mimes:jpeg,png,jpg,webp','max:10240'],
        ];
    }
}
