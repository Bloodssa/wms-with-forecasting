<?php

namespace App\Http\Requests\WarrantyServiceRecord;

use App\Enum\ServiceType;
use App\Models\WarrantyServiceRecord;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreWarrantyServiceRecordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', WarrantyServiceRecord::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'service_type' => ['required', Rule::enum(ServiceType::class)],
            'parts_cost' => ['nullable', 'numeric', 'min:0'],
            'labor_cost' => ['nullable', 'numeric', 'min:0'],

            'total_cost' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }

    /**
     * At least one cost figure has to be present, otherwise there's nothing to record.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if (blank($this->input('total_cost')) && blank($this->input('parts_cost')) && blank($this->input('labor_cost'))) {
                $validator->errors()->add('total_cost', 'Provide a total cost, or parts/labor cost to calculate one.');
            }
        });
    }
}
