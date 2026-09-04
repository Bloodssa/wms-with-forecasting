<?php

namespace App\Repositories\WarrantyServiceRecord;

use App\Models\WarrantyServiceRecord;
use Illuminate\Support\Collection;

/**
 * Basic data access for ACTUAL repair/service cost records.
 */
interface WarrantyServiceRecordRepositoryInterface
{
    public function create(array $data): WarrantyServiceRecord;

    public function getForInquiry(int $inquiryId): Collection;

    public function find(int $id): WarrantyServiceRecord;
}
