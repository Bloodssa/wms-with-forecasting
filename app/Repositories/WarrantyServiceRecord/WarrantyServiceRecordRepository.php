<?php

namespace App\Repositories\WarrantyServiceRecord;

use App\Models\WarrantyServiceRecord;
use Illuminate\Support\Collection;

class WarrantyServiceRecordRepository implements WarrantyServiceRecordRepositoryInterface
{
    public function create(array $data): WarrantyServiceRecord
    {
        return WarrantyServiceRecord::create($data);
    }

    public function getForInquiry(int $inquiryId): Collection
    {
        return WarrantyServiceRecord::where('warranty_inquiries_id', $inquiryId)
            ->latest()
            ->get();
    }

    public function find(int $id): WarrantyServiceRecord
    {
        return WarrantyServiceRecord::findOrFail($id);
    }
}
