<?php

namespace App\Enum;

enum InquiryStatusType:string
{
    case OPEN = 'open';
    case PENDING = 'pending';
    case IN_PROGRESS = 'in-progress';
    case CLOSED = 'closed';
    case RESOLVED = 'resolved';
    case REPLACED = 'replaced';

    public function label(): string 
    {
        return ucfirst($this->value);
    }

    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn($case) => [
            $case->value => $case->label()
        ])->toArray();
    }
}
