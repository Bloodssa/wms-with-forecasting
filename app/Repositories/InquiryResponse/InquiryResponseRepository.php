<?php

namespace App\Repositories\InquiryResponse;

use App\Models\InquiryResponse;
use Illuminate\Database\Eloquent\Collection;
use \App\Models\WarrantyInquiries;

class InquiryResponseRepository implements InquiryResponseRepositoryInterface
{
    public function countUnreadGlobal(): int
    {
        return InquiryResponse::whereNull('read_at')->count();
    }
    
    public function create(array $data): InquiryResponse
    {
        return InquiryResponse::create($data);
    }

    public function find(int $id): ?InquiryResponse
    {
        return InquiryResponse::with('warrantyInquiries')
            ->find($id);
    }

    public function markUnreadForInquiry(int $inquiryId): void
    {
        $inquiry = WarrantyInquiries::find($inquiryId);

        if ($inquiry) {
            $inquiry->update([
                'read_at' => null,
            ]);
        }
    }

    public function hasUnreadFromOthers(string $inquiryId, int $userId): bool
    {
        return InquiryResponse::where('warranty_inquiries_id', $inquiryId)
            ->whereNull('read_at')
            ->where('user_id', '!=', $userId)
            ->exists();
    }

    public function markReadFromOthers(string $inquiryId, int $userId): int
    {
        return InquiryResponse::where('warranty_inquiries_id', $inquiryId)
            ->whereNull('read_at')
            ->where('user_id', '!=', $userId)
            ->update([
                'read_at' => now(),
            ]);
    }

    public function createStatusResponse(int $inquiryId, int $userId, string $message, mixed $type): InquiryResponse
    {
        return InquiryResponse::create([
            'warranty_inquiries_id' => $inquiryId,
            'user_id' => $userId,
            'message' => $message,
            'type' => $type,
        ]);
    }

    public function getUnreadCount(): int
    {
        return InquiryResponse::whereNull('read_at')->count();
    }
}
