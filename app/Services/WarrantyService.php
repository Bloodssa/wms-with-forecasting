<?php

namespace App\Services;

use App\Enum\InquiryResponseType;
use App\Enum\InquiryStatusType;
use App\Enum\WarrantyStatusType;
use App\Events\InquiryResponseSent;
use App\Exceptions\WarrantyOperationException;
use App\Mail\WarrantyInvitation;
use App\Models\InquiryResponse;
use App\Models\Product;
use App\Models\User;
use App\Models\Warranty;
use App\Repositories\Warranty\WarrantyRepositoryInterface;
use App\Repositories\Inquiry\InquiryRepositoryInterface;
use App\Repositories\InquiryResponse\InquiryResponseRepositoryInterface;
use App\Repositories\User\UserRepositoryInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;

class WarrantyService
{
    public function __construct(
        private readonly WarrantyRepositoryInterface $warrantyRepository,
        private readonly InquiryRepositoryInterface $inquiryRepository,
        private readonly InquiryResponseRepositoryInterface $inquiryResponseRepository,
        private readonly UserRepositoryInterface $userRepository,
    ) {}

    /**
     * Helper for the claim of customer and set the warranty invitation token to null means its already claimed
     */
    public function claimWarranty(User $user)
    {
        // check if there is a set claim_email in the session if not set to current google login email
        $warrantyIds = session('claim_warranty_id', []);

        if (!is_array($warrantyIds)) {
            $warrantyIds = array_filter([$warrantyIds]);
        }

        // if session is empty try to get the unclaimed warranty with the same claim email
        if (empty($warrantyIds)) {
            $warrantyIds = $this->warrantyRepository->findUnclaimedIdsByEmail(strtolower(trim($user->email)));
        }

        // there is nothing to claim
        if (empty($warrantyIds)) {
            Log::info('No warranty IDs found in session.');
            return;
        }

        $claimEmail = session('claim_email');
        if ($claimEmail && !hash_equals(strtolower(trim($claimEmail)), strtolower(trim($user->email)))) {
            Log::warning('Warranty claim email mismatch.', [
                'claim_email' => $claimEmail,
                'user_email' => $user->email,
                'warranty_ids' => $warrantyIds,
            ]);

            return;
        }

        Log::info('Claiming warranties.', [
            'warranty_ids' => $warrantyIds,
            'user_id' => $user->id,
        ]);

        // claim every warranty id if found in same email
        $this->warrantyRepository->claimForUser($user, $warrantyIds);

        // remove the claim_email session set in the showRegister
        session()->forget([
            'claim_email',
            'claim_warranty_id'
        ]);
    }

    public function updateWarranty(Warranty $warranty, array $validated): Warranty
    {
        return $this->warrantyRepository->update($warranty, $validated);
    }

    public function destroyWarranty(Warranty $warranty): bool
    {
        return $this->warrantyRepository->delete($warranty);
    }

    public function archiveWarranty(Warranty $warranty): Warranty
    {
        return $this->warrantyRepository->archive($warranty);
    }

    public function unarchiveWarranty(Warranty $warranty): Warranty
    {
        $warranty->archived_at = null;

        $computedStatus = $this->computeStatus($warranty);

        return $this->warrantyRepository->unarchive($warranty, $computedStatus->value);
    }

    /**
     * resolve the warranty and eligibility context needed to render the "new inquiry" form.
     *
     * @throws WarrantyOperationException
     */
    public function getInquiryCreationContext(string $warrantyId, int $userId): Warranty
    {
        $warranty = $this->warrantyRepository->findWithProductCategoryForUser($warrantyId, $userId);

        if (! $warranty) {
            throw new WarrantyOperationException('Unauthorized access to warranty.');
        }

        if (now()->greaterThan($warranty->expiry_date)) {
            throw new WarrantyOperationException('This warranty has expired and cannot accept inquiries.', 'inquiries');
        }

        $finalStatuses = collect(InquiryStatusType::cases())
            ->filter(fn($status) => $status->isFinal())
            ->map(fn($status) => $status->value)
            ->toArray();

        if ($this->inquiryRepository->hasActiveForWarranty($warranty->id, $finalStatuses)) {
            throw new WarrantyOperationException('You already have an active inquiry for this product.', 'inquiries');
        }

        return $warranty;
    }

    public function getClaimableWarranties(string $email): Collection
    {
        return $this->warrantyRepository->getUnclaimedByEmail($email);
    }

    /**
     * Register a batch of warranties for a claim email, sending the registration/invitation email.
     *
     * @return array{createdIds: array, warranties: Collection}
     */
    public function registerWarranties(array $data): array
    {
        $createdWarranties = [];
        $user = null;

        DB::transaction(function () use ($data, &$user, &$createdWarranties) {

            $user = $this->userRepository->findByEmail($data['claim_email']);

            foreach ($data['multiple_products'] as $item) {

                $product = Product::findOrFail($item['product_id']);

                $purchaseDate = now();
                $expiryDate = $purchaseDate->copy()->addMonths($product->warranty_duration);

                $warranty = $this->warrantyRepository->create([
                    'user_id' => $user?->id,
                    'product_id' => $product->id,
                    'claim_email' => $data['claim_email'],
                    'serial_number' => $item['serial_number'],
                    'purchase_date' => $purchaseDate,
                    'purchase_price' => $item['price'],
                    'expiry_date' => $expiryDate,
                    'status' => $user ? WarrantyStatusType::ACTIVE : WarrantyStatusType::PENDING,
                    'is_claimed' => (bool) $user,
                ]);

                $createdWarranties[] = $warranty->id;
            }
        });

        $warranties = $this->warrantyRepository->getWithProductByIds($createdWarranties);

        Log::info('User Warranty Send mail: ' . $data['claim_email']);

        $registrationLink = URL::temporarySignedRoute(
            'customer.claim',
            now()->addDays(60),
            ['email' => $data['claim_email']]
        );

        Mail::to($data['claim_email'])->send(new WarrantyInvitation(
            $warranties,
            $data['claim_email'],
            $registrationLink
        ));

        return [
            'createdIds' => $createdWarranties,
            'warranties' => $warranties,
        ];
    }

    /**
     * @return array{warranties: Collection, email: string, total: float|int, date: \Illuminate\Support\Carbon}
     */
    public function getInvoiceData(array $ids): array
    {
        $warranties = $this->warrantyRepository->getWithProductByIds($ids);

        if ($warranties->isEmpty()) {
            throw new WarrantyOperationException('No warranties found.');
        }

        return [
            'warranties' => $warranties,
            'email' => $warranties->first()->claim_email,
            'total' => $warranties->sum('purchase_price'),
            'date' => now(),
        ];
    }

    public function createResponse(array $data, array $attachments, int $userId): InquiryResponse
    {
        $attachmentPaths = [];

        foreach ($attachments as $file) {
            $attachmentPaths[] = $file->store('inquiries', 'public');
        }

        $data['user_id'] = $userId;
        $data['attachments'] = $attachmentPaths;

        $response = $this->inquiryResponseRepository->create($data);

        // mark as unread
        $this->inquiryResponseRepository->markUnreadForInquiry($response?->warranty_inquiries_id);

        broadcast(new InquiryResponseSent($response))->toOthers();

        return $response;
    }

    public function markInquiryRead(string $inquiryId, int $userId): void
    {
        if ($this->inquiryResponseRepository->hasUnreadFromOthers($inquiryId, $userId)) {
            $this->inquiryResponseRepository->markReadFromOthers($inquiryId, $userId);
            $this->inquiryRepository->markRead($inquiryId);
        }
    }

    /**
     * @throws WarrantyOperationException
     */
    public function transitionInquiryStatus(string $id, string $newStatusValue, ?string $resolvedMessage, int $userId): InquiryResponse
    {
        $inquiry = $this->inquiryRepository->find($id);
        $previousStatus = $inquiry->status;
        $newStatus = InquiryStatusType::from($newStatusValue);

        if (! $previousStatus->canTransitionTo($newStatus)) {
            throw new WarrantyOperationException(
                "Invalid transition: You cannot move the status from " . $previousStatus->label() . " to " . $newStatus->label() . "."
            );
        }

        $this->inquiryRepository->update($inquiry, ['status' => $newStatusValue]);

        $message = $resolvedMessage ?? "Status changed from " . $previousStatus->label() . " to " . $newStatus->label();
        $type = $resolvedMessage ? InquiryResponseType::SOLUTION : InquiryResponseType::UPDATES;

        $response = $this->inquiryResponseRepository->create([
            'warranty_inquiries_id' => $inquiry->id,
            'user_id' => $userId,
            'message' => $message,
            'type' => $type,
        ]);

        broadcast(new InquiryResponseSent($response))->toOthers();

        return $response;
    }

    public function cancelInquiry(int $inquiryId, string $message, int $userId): void 
    {
        $inquiry = $this->inquiryRepository->findOwnedByUser($inquiryId, $userId);

        if (! $inquiry)
            throw new \Exception('Inquiry not found.');
        

        if ($inquiry->status->isFinal()) 
            throw new \Exception('This inquiry can no longer be cancelled.');
        

        $this->inquiryRepository->updateStatus($inquiry->id,InquiryStatusType::CLOSED);

        $this->inquiryResponseRepository->create([
            'warranty_inquiries_id' => $inquiry->id,
            'user_id' => $userId,
            'message' => "User cancelled inquiry: {$message}",
            'type' => InquiryResponseType::SOLUTION,
        ]);
    }

    /**
     * @throws WarrantyOperationException
     */
    public function claimBySerialAndEmail(string $serialNumber, string $email, int $userId): Warranty
    {
        $warranty = $this->warrantyRepository->findPendingBySerialAndEmail($serialNumber, $email);

        if (! $warranty) {
            throw new WarrantyOperationException('We could not find a pending warranty with this serial number and email combination.');
        }

        return $this->warrantyRepository->claim($warranty, $userId);
    }


    // helper for getting the type of the warranty status
    private function computeStatus(Warranty $warranty): WarrantyStatusType
    {
        if ($warranty->archived_at) {
            return WarrantyStatusType::ARCHIVED;
        }

        $now = now();

        if ($warranty->expiry_date <= $now) {
            return WarrantyStatusType::EXPIRED;
        }

        if ($warranty->expiry_date <= $now->copy()->addMonth()) {
            return WarrantyStatusType::NEAR_EXPIRY;
        }

        return WarrantyStatusType::ACTIVE;
    }
}
