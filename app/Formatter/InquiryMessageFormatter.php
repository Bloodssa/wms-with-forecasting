<?php

namespace App\Formatter;
use Illuminate\Support\Collection;

class InquiryMessageFormatter
{
    /**
     * Build a chronological message timeline out of a set of warranty inquiries
     * and their responses (admin/customer updates, solutions, messages).
     */
    public function format(iterable $inquiries): Collection
    {
        $messages = collect();

        foreach ($inquiries as $inquiry) {
            // append the inquiry message first to the collections for first message
            $messages->push($this->formatMessage(
                'message',
                $inquiry->message,
                $inquiry->user,
                $inquiry->created_at,
                $inquiry->attachments,
                $inquiry->status
            ));

            // inquiry responses with the type if updates, solution or message of admin or customer
            foreach ($inquiry->responses as $response) {
                $messages->push($this->formatMessage(
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
     * Map a single message/response into the shared display shape.
     *
     * @param string $type type of the message
     * @param string $message the message
     * @param \App\Models\User $user the one who responded
     * @param \DateTimeInterface $date when it was created
     * @param mixed $attachments json/array of attachments such as inquiry images
     * @param mixed $status the status of the inquiry
     */
    private function formatMessage($type, $message, $user, $date, $attachments = [], $status = null): object
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

