<?php

namespace App\Services;

use App\Enum\InquiryStatusType;
use App\Models\Warranty;
use Illuminate\Http\UploadedFile;
use App\Models\WarrantyInquiries;
use App\Repositories\Inquiry\InquiryRepositoryInterface;

class InquiryService
{
    public function __construct(
        private readonly InquiryRepositoryInterface $inquiryRepository,
    ) {}

    public function createInquiry(array $data, array $files, int $userId): WarrantyInquiries 
    {
        $warranty = Warranty::query()
            ->where('id', $data['warranty_id'])
            ->where('user_id', $userId)
            ->first();

        if (!$warranty) 
            throw new \RuntimeException(
                'You are not allowed to access this warranty.'
            );

        if (now()->greaterThan($warranty->expiry_date)) 
            throw new \RuntimeException(
                'This warranty has already expired and cannot accept new inquiries.'
            );

        $attachmentPaths = $this->storeAttachments($files);

        return $this->inquiryRepository->create([
            'warranty_id' => $data['warranty_id'],
            'message' => $data['message'],
            'status' => InquiryStatusType::OPEN,
            'attachments' => $attachmentPaths,
            'read_at' => null,
        ]);
    }

    

    private function storeAttachments(array $files): array
    {
        $paths = [];

        foreach ($files as $file) {
            if ($file instanceof UploadedFile)
                $paths[] = $file->store('inquiries', 'public');
        }

        return $paths;
    }
}
