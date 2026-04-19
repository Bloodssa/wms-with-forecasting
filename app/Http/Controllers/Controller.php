<?php

namespace App\Http\Controllers;

use App\Enum\WarrantyStatusType;
use App\Models\User;
use App\Models\Warranty;

abstract class Controller
{
    /**
     * Helper for the claim of customer and set the warranty invitation token to null means its already claimed
     */
    protected function claimWarranty(User $user)
    {
        // check if there is a set claim_email in the session if not set to current google login email
        $warrantyId = session('claim_warranty');

        if (!$warrantyId) {
            return;
        }

        $warrantyClaim = Warranty::where('id', $warrantyId)->where('is_claimed', false)->first();

        if ($warrantyClaim) {
            // set null for one claim only
            $warrantyClaim->update([
                'user_id' => $user->id,
                'is_claimed' => true,
                'status' => WarrantyStatusType::ACTIVE->value
            ]);
        }

        // remove the claim_email session set in the showRegister
        session()->forget('claim_warranty');
    }

    /**
     * Temporary Messages Helper
     */
    protected function inquiryMessages($inquiries)
    {
        $messages = collect();

        foreach ($inquiries as $inquiry) {
            // append the inquiry message first to the collections for first message
            $messages->push($this->formatInquiryMessage(
                'message',
                $inquiry->message,
                $inquiry->user,
                $inquiry->created_at,
                $inquiry->attachments,
                $inquiry->status
            ));

            // inquiry responses with the type if updates, solution or message of admin or customer
            foreach ($inquiry->responses as $response) {
                $messages->push($this->formatInquiryMessage(
                    $response->type,
                    $response->message,
                    $response->user,
                    $response->created_at,
                    $response->attachments,
                    $inquiry->status
                ));
            }
        }

        // sort created because this is all the warranty inquiries to be in sequence of the date
        return $messages->sortBy('created_at')->values();
    }

    /**
     * Helper for mapping the inquiry message format
     * 
     * @param string type of the message
     * @param string message the message
     * @param App/Models/User the onw who respond
     * @param date when its created
     * @param json or casted into array attachments such us image of the inquiry
     * @param status the status of inquiry
     */
    private function formatInquiryMessage($type, $message, $user, $date, $attachments = [], $status = null): object
    {
        return (object)[
            'type' => $type,
            'message' => $message,
            'user' => $user,
            'created_at' => $date,
            'attachments' => $attachments ?? [],
            'status' => $status,
        ];
    }
}
