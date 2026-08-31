<?php

namespace App\Repositories\InquiryResponse;

use App\Models\InquiryResponse;

interface InquiryResponseRepositoryInterface
{
    public function countUnreadGlobal(): int;
    
    public function create(array $data): InquiryResponse;

    public function find(int $id): ?InquiryResponse;

    public function markUnreadForInquiry(int $inquiryId): void;

    public function hasUnreadFromOthers(string $inquiryId, int $userId): bool;

    public function markReadFromOthers(string $inquiryId, int $userId): int;

    public function createStatusResponse(int $inquiryId, int $userId, string $message, mixed $type): InquiryResponse;

    public function getUnreadCount(): int;
}
