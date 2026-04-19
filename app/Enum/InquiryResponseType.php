<?php

namespace App\Enum;

enum InquiryResponseType: string
{
    case MESSAGE = 'message';
    case UPDATES = 'updates';
    case SOLUTION = 'solution';

    public function label(): string
    {
        return $this->value;
    }

    public function getTypeAttribute($value): InquiryResponseType
    {
        return InquiryResponseType::from($value);
    }
}
